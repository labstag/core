<?php

namespace Labstag\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Labstag\Message\ClearCacheMessage;
use Labstag\Message\DeleteOldFileMessage;
use Labstag\Repository\ServiceEntityRepositoryAbstract;
use Labstag\Service\FileService;
use Labstag\Service\SiteService;
use Labstag\Service\UserService;
use Labstag\Service\WorkflowService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class BackController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected UserService $userService,
        protected FileService $fileService,
        protected WorkflowService $workflowService,
        protected SiteService $siteService,
    )
    {
    }

    #[Route(
        '/admin/{_locale}/blank',
        name: 'admin_blank',
        defaults: ['_locale' => 'fr']
    )]
    public function blank(): Response
    {
        return $this->render('admin/blank.html.twig', []);
    }

    #[Route(
        '/admin/{_locale}/purge',
        name: 'admin_cacheclear',
        defaults: ['_locale' => 'fr']
    )]
    public function cacheclear(MessageBusInterface $messageBus, Request $request): Response
    {
        $messageBus->dispatch(new ClearCacheMessage());
        $messageBus->dispatch(new DeleteOldFileMessage());
        $this->addFlash('success', new TranslatableMessage('Cache cleared'));
        if ($request->headers->has('referer')) {
            $url = $request->headers->get('referer');
            if (is_string($url) && '' !== $url) {
                return $this->redirect($url);
            }
        }

        return $this->redirectToRoute('admin');
    }

    #[Route(
        '/admin/{_locale}/restore',
        name: 'admin_restore',
        defaults: ['_locale' => 'fr']
    )]
    #[Route(
        '/admin/{_locale}/empty',
        name: 'admin_empty',
        defaults: ['_locale' => 'fr']
    )]
    public function emptyOrRestore(Request $request): Response
    {
        $this->entityManager->getFilters()->disable('softdeleteable');
        $referer = $request->headers->get('referer');
        if (is_null($referer) || '' === $referer || '0' === $referer) {
            return $this->redirectToRoute('admin');
        }

        $routeName = $request->query->get('routeName');
        $entity    = $request->attributes->get('entity', null);
        $uuid      = $request->attributes->get('uuid', null);
        match ($routeName) {
            'admin_restore' => $this->adminRestore($entity, $uuid),
            'admin_empty'   => $this->adminEmpty($entity),
            default         => throw new Exception(new TranslatableMessage('Route not found')),
        };

        return $this->redirect($referer);
    }

    #[Route(
        '/admin/{_locale}/workflow',
        name: 'admin_workflow',
        defaults: ['_locale' => 'fr']
    )]
    public function workflow(Request $request): Response
    {
        $referer = $request->headers->get('referer');
        if (is_null($referer) || '' === $referer || '0' === $referer) {
            return $this->redirectToRoute('admin');
        }

        $entity     = $request->query->get('entity', null);
        $transition = $request->query->get('transition', null);
        $uid        = $request->query->get('uid', null);

        $this->workflowService->change($entity, $transition, $uid);

        return $this->redirect($referer);
    }

    protected function adminEmpty(string $entity): void
    {
        $serviceEntityRepositoryAbstract = $this->getRepository($entity);
        $all                             = $serviceEntityRepositoryAbstract->findDeleted();
        foreach ($all as $row) {
            $serviceEntityRepositoryAbstract->remove($row);
        }

        $serviceEntityRepositoryAbstract->flush();
    }

    protected function adminRestore(string $entity, mixed $uuid): void
    {
        $serviceEntityRepositoryAbstract = $this->getRepository($entity);
        $data                            = $serviceEntityRepositoryAbstract->find($uuid);
        if (is_null($data)) {
            throw new Exception(new TranslatableMessage('Data not found'));
        }

        if (!method_exists($data, 'isDeleted')) {
            throw new Exception(new TranslatableMessage('Method not found'));
        }

        if ($data->isDeleted()) {
            $data->setDeletedAt(null);
            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }
    }

    /**
     * @return ServiceEntityRepositoryAbstract<object>
     */
    protected function getRepository(string $entity): ServiceEntityRepositoryAbstract
    {
        $entityRepository = $this->entityManager->getRepository($entity);
        if (!$entityRepository instanceof ServiceEntityRepositoryAbstract) {
            throw new Exception('Repository not found');
        }

        return $entityRepository;
    }
}
