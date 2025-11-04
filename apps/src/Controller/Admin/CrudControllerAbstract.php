<?php

namespace Labstag\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Labstag\Controller\Admin\Factory\CrudFieldFactory;
use Labstag\Controller\Admin\Factory\LinkActionFactory;
use Labstag\Controller\Admin\Traits\ParagraphAdminTrait;
use Labstag\Controller\Admin\Traits\TrashActionsTrait;
use Labstag\Entity\Meta;
use Labstag\Entity\Paragraph;
use Labstag\Repository\ParagraphRepository;
use Labstag\Repository\RepositoryAbstract;
use Labstag\Service\BlockService;
use Labstag\Service\EmailService;
use Labstag\Service\FileService;
use Labstag\Service\FormService;
use Labstag\Service\MovieService;
use Labstag\Service\ParagraphService;
use Labstag\Service\SeasonService;
use Labstag\Service\SecurityService;
use Labstag\Service\SerieService;
use Labstag\Service\SiteService;
use Labstag\Service\SlugService;
use Labstag\Service\UserService;
use Labstag\Service\WorkflowService;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AutoconfigureTag('labstag.admincontroller')]
/**
 * @template TEntity of object
 *
 * @extends AbstractCrudController<TEntity>
 */
abstract class CrudControllerAbstract extends AbstractCrudController
{
    use ParagraphAdminTrait;
    use TrashActionsTrait;

    public function __construct(
        protected EmailService $emailService,
        protected SerieService $serieService,
        protected FormService $formService,
        protected FileService $fileService,
        protected SiteService $siteService,
        protected SlugService $slugService,
        protected SeasonService $seasonService,
        protected SecurityService $securityService,
        protected BlockService $blockService,
        protected MovieService $movieService,
        protected ParagraphService $paragraphService,
        protected WorkflowService $workflowService,
        protected RequestStack $requestStack,
        protected UserService $userService,
        protected CrudFieldFactory $crudFieldFactory,
        protected LinkActionFactory $linkActionFactory,
        protected AdminUrlGenerator $adminUrlGenerator,
        protected ManagerRegistry $managerRegistry,
    )
    {
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud->addFormTheme('admin/form.html.twig');
        $crud->renderContentMaximized();
        $crud->renderSidebarMinimized();

        $request = $this->requestStack->getCurrentRequest();
        $limit   = $request->query->get('limit', 20);

        $crud->setPaginatorPageSize($limit);

        return $crud;
    }

    #[\Override]
    public function createEntity(string $entityFqcn): object
    {
        $entity = new $entityFqcn();
        $this->workflowService->init($entity);
        $reflectionClass = new ReflectionClass($entity);
        if ($reflectionClass->hasMethod('setMeta')) {
            $meta = new Meta();
            $entity->setMeta($meta);
        }

        if ($reflectionClass->hasMethod('setRefuser')) {
            $entity->setRefuser($this->getUser());
        }

        return $entity;
    }

    #[\Override]
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fieldCollection,
        FilterCollection $filterCollection,
    ): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fieldCollection, $filterCollection);
        $queryBuilder = $this->filterListeTrash($searchDto, $queryBuilder);

        return $this->filterListRefUser($queryBuilder);
    }

    protected function configureActionsBtn(Actions $actions): void
    {
        $actions->add(Crud::PAGE_EDIT, Action::INDEX);
        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
        $actions->add(Crud::PAGE_NEW, Action::INDEX);
    }

    protected function configureActionsTrash(Actions $actions): void
    {
        $this->configureTrashActions($actions, $this->requestStack->getCurrentRequest(), $this->adminUrlGenerator);
    }

    /**
     * Backward compatibility helper - new code should call getRepository() or inject repositories directly.
     *
     * @return RepositoryAbstract<object>
     */
    protected function getRepository(?string $entity = null): object
    {
        $entity ??= static::getEntityFqcn();

        return $this->getDoctrineRepository($entity);
    }

    protected function getRepositoryParagraph(): ParagraphRepository
    {
        $repositoryAbstract = $this->getDoctrineRepository(Paragraph::class);
        assert($repositoryAbstract instanceof ParagraphRepository);

        return $repositoryAbstract;
    }

    protected function isSuperAdmin(): bool
    {
        $user = $this->getUser();
        if (!is_object($user)) {
            return false;
        }

        return in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true);
    }

    protected function linkw3CValidator(object $entity): RedirectResponse
    {
        $slug = $this->slugService->forEntity($entity);

        return $this->redirect(
            'https://validator.w3.org/nu/?doc=' . $this->generateUrl(
                'front',
                ['slug' => $slug],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    protected function publicLink(object $entity): RedirectResponse
    {
        $slug = $this->slugService->forEntity($entity);

        return $this->redirectToRoute(
            'front',
            ['slug' => $slug]
        );
    }

    protected function setActionPublic(Actions $actions, string $urlW3c, string $urlPublic): void
    {
        $actions->add(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE);

        $action = $this->linkActionFactory->createPublicAction($urlPublic);
        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);

        $w3cAction = $this->linkActionFactory->createW3cAction($urlW3c);
        $actions->add(Crud::PAGE_EDIT, $w3cAction);
        $actions->add(Crud::PAGE_INDEX, $w3cAction);
        $actions->add(Crud::PAGE_DETAIL, $w3cAction);
    }

    protected function setEditDetail(Actions $actions): void
    {
        $actions->add(Crud::PAGE_EDIT, Action::DETAIL);
    }

    private function filterListeTrash(SearchDto $searchDto, QueryBuilder $queryBuilder): QueryBuilder
    {
        return $this->filterTrash($searchDto, $queryBuilder);
    }

    private function filterListRefUser(QueryBuilder $queryBuilder): QueryBuilder
    {
        // Ownership filter now handled by dedicated extension (OwnerRestrictionExtension)
        return $queryBuilder;
    }

    /**
     * Internal helper to fetch a Doctrine repository with generics-like safety.
     */
    /**
     * @return RepositoryAbstract<object>
     */
    private function getDoctrineRepository(string $entity): object
    {
        $objectManager = $this->managerRegistry->getManagerForClass($entity);
        /** @var RepositoryAbstract<object> $objectRepository */
        $objectRepository = $objectManager->getRepository($entity);
        assert(!is_null($objectRepository));

        return $objectRepository;
    }
}
