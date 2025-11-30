<?php

namespace Labstag\Controller;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use Exception;
use Labstag\Api\IgdbApi;
use Labstag\Message\ClearCacheMessage;
use Labstag\Message\DeleteOldFileMessage;
use Labstag\Repository\GroupRepository;
use Labstag\Repository\PermissionRepository;
use Labstag\Repository\RepositoryAbstract;
use Labstag\Service\FileService;
use Labstag\Service\SiteService;
use Labstag\Service\UserService;
use Labstag\Service\WorkflowService;
use ReflectionClass;
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
        protected IgdbApi $igdbApi,
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
        $entity    = $request->attributes->get('entity');
        $uuid      = $request->attributes->get('uuid');
        match ($routeName) {
            'admin_restore' => $this->adminRestore($entity, $uuid),
            'admin_empty'   => $this->adminEmpty($entity),
            default         => throw new Exception(new TranslatableMessage('Route not found')),
        };

        return $this->redirect($referer);
    }

    #[Route(
        '/admin/{_locale}/permission',
        name: 'admin_permission',
        defaults: ['_locale' => 'fr']
    )]
    public function permission(
        Request $request,
        PermissionRepository $permissionRepository,
        GroupRepository $groupRepository,
    ): Response
    {
        if ($request->isMethod('POST')) {
            $groupId      = $request->query->get('groupId');
            $permissionId = $request->query->get('permissionId');

            $group      = $groupRepository->find($groupId);
            $permission = $permissionRepository->find($permissionId);

            if (!$group || !$permission) {
                return $this->json(
                    [
                        'success' => false,
                        'message' => 'Group or Permission not found',
                    ],
                    Response::HTTP_NOT_FOUND
                );
            }

            $permissions = $group->getPermissions();
            match ($permissions->contains($permission)) {
                true  => $group->removePermission($permission),
                false => $group->addPermission($permission),
            };

            $groupRepository->save($group);

            return $this->json(
                ['success' => true]
            );
        }

        $permissions = $permissionRepository->findBy(
            [],
            ['title' => 'ASC']
        );
        $groups = $groupRepository->findAll();
        $data   = [];
        foreach ($permissions as $permission) {
            [
                $group,
                $code,
            ]                    = explode('_', (string) $permission->getTitle(), 2);
            $data[$group][$code] = $permission;
        }

        return $this->render(
            'admin/permission.html.twig',
            [
                'assets' => Asset::fromEasyAdminAssetPackage('field-boolean.js'),
                'data'   => $data,
                'groups' => $groups,
            ]
        );
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

        $entity     = $request->query->get('entity');
        $transition = $request->query->get('transition');
        $uid        = $request->query->get('uid');

        $this->workflowService->change($entity, $transition, $uid);

        return $this->redirect($referer);
    }

    protected function adminEmpty(string $object): void
    {
        $reflectionClass    = new ReflectionClass($object);
        $parentClass        = $reflectionClass->getParentClass();
        $repositoryAbstract = $this->getRepository($object);
        $class              = null;
        if ($parentClass instanceof ReflectionClass) {
            $repositoryAbstract = $this->getRepository($parentClass->getName());
            $class              = $object;
        }

        $all = $repositoryAbstract->findDeleted($class);
        foreach ($all as $row) {
            $repositoryAbstract->remove($row);
        }

        $repositoryAbstract->flush();
    }

    protected function adminRestore(string $entity, mixed $uuid): void
    {
        $repositoryAbstract              = $this->getRepository($entity);
        $data                            = $repositoryAbstract->find($uuid);
        if (is_null($data)) {
            throw new Exception(new TranslatableMessage('Data not found'));
        }

        if (!method_exists($data, 'isDeleted')) {
            throw new Exception(new TranslatableMessage('Method not found'));
        }

        if ($data->isDeleted()) {
            $data->setDeletedAt(null);
            if (method_exists($data, 'getMeta')) {
                $meta = $data->getMeta();
                $this->adminRestore($meta::class, $meta->getId());
            }

            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }
    }

    /**
     * @return RepositoryAbstract<object>
     */
    protected function getRepository(string $entity): object
    {
        $entityRepository = $this->entityManager->getRepository($entity);
        if (is_null($entityRepository)) {
            throw new Exception('Repository not found');
        }

        return $entityRepository;
    }
}
