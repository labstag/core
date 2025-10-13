<?php

namespace Labstag\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Labstag\Repository\Abstract\ServiceEntityRepositoryLib;
use Labstag\Service\FileService;
use Labstag\Service\SiteService;
use Labstag\Service\UserService;
use Labstag\Service\WorkflowService;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
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
    public function cacheclear(KernelInterface $kernel): Response
    {
        $total = $this->fileService->deletedFileByEntities();
        if (0 !== $total) {
            $this->addFlash(
                'success',
                new TranslatableMessage(
                    '%total% file(s) deleted',
                    ['%total%' => $total]
                )
            );
        }

        // execution de la commande en console
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $arrayInput = new ArrayInput(['cache:clear']);

        $bufferedOutput = new BufferedOutput();
        $application->run($arrayInput, $bufferedOutput);

        $this->addFlash('success', new TranslatableMessage('Cache cleared'));

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
        $serviceEntityRepositoryLib = $this->getRepository($entity);
        $all                        = $serviceEntityRepositoryLib->findDeleted();
        foreach ($all as $row) {
            $serviceEntityRepositoryLib->remove($row);
        }

        $serviceEntityRepositoryLib->flush();
    }

    protected function adminRestore(string $entity, mixed $uuid): void
    {
        $serviceEntityRepositoryLib = $this->getRepository($entity);
        $data                       = $serviceEntityRepositoryLib->find($uuid);
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
     * @return ServiceEntityRepositoryLib<object>
     */
    protected function getRepository(string $entity): ServiceEntityRepositoryLib
    {
        $entityRepository = $this->entityManager->getRepository($entity);
        if (!$entityRepository instanceof ServiceEntityRepositoryLib) {
            throw new Exception('Repository not found');
        }

        return $entityRepository;
    }
}
