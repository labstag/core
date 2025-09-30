<?php

namespace Labstag\Lib;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Labstag\Entity\Paragraph;
use Labstag\Lib\Admin\Factory\CrudFieldFactory;
use Labstag\Lib\Admin\Factory\LinkActionFactory;
use Labstag\Lib\Admin\Traits\ParagraphAdminTrait;
use Labstag\Lib\Admin\Traits\TrashActionsTrait;
use Labstag\Repository\ParagraphRepository;
use Labstag\Service\BlockService;
use Labstag\Service\EmailService;
use Labstag\Service\FileService;
use Labstag\Service\FormService;
use Labstag\Service\MovieService;
use Labstag\Service\ParagraphService;
use Labstag\Service\SecurityService;
use Labstag\Service\SiteService;
use Labstag\Service\SlugService;
use Labstag\Service\UserService;
use Labstag\Service\WorkflowService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatableMessage;

abstract class AbstractCrudControllerLib extends AbstractCrudController
{
    use ParagraphAdminTrait;
    use TrashActionsTrait;

    public function __construct(
        protected EmailService $emailService,
        protected FormService $formService,
        protected FileService $fileService,
        protected SiteService $siteService,
        protected SlugService $slugService,
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
    ) {
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->addFormTheme('admin/form.html.twig');

        return $crud;
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fieldCollection,
        FilterCollection $filterCollection,
    ): QueryBuilder {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fieldCollection, $filterCollection);
        $queryBuilder = $this->filterListeTrash($searchDto, $queryBuilder);

        return $this->filterListRefUser($queryBuilder, $entityDto);
    }

    // Tabs helpers retained (principal) for consistent UI; dateSet now provided by CrudFieldFactory.
    protected function addTabPrincipal(): FormField { return FormField::addTab(new TranslatableMessage('Principal')); }

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
     */
    protected function getRepository(?string $entity = null): ServiceEntityRepositoryLib
    {
        $entity ??= static::getEntityFqcn();

        return $this->getDoctrineRepository($entity);
    }

    protected function getRepositoryParagraph(): ParagraphRepository
    {
        return $this->getDoctrineRepository(Paragraph::class);
    }

    protected function linkw3CValidator(object $entity): RedirectResponse
    {
        $slug = $this->slugService->forEntity($entity);

        return $this->redirect('https://validator.w3.org/nu/?doc=' . $this->generateUrl('front', ['slug' => $slug], UrlGeneratorInterface::ABSOLUTE_URL));
    }

    protected function publicLink(object $entity): RedirectResponse
    {
        $slug = $this->slugService->forEntity($entity);

        return $this->redirectToRoute('front', ['slug' => $slug]);
    }

    protected function setActionPublic(Actions $actions, string $urlW3c, string $urlPublic): void
    {
        $actions->add(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE);

        $action = $this->setLinkPublicAction($urlPublic);
        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);

        $w3cAction = $this->setW3cValidatorAction($urlW3c);
        if ($w3cAction) {
            $actions->add(Crud::PAGE_EDIT, $w3cAction);
            $actions->add(Crud::PAGE_INDEX, $w3cAction);
            $actions->add(Crud::PAGE_DETAIL, $w3cAction);
        }
    }

    protected function setEditDetail(Actions $actions): void
    {
        $actions->add(Crud::PAGE_EDIT, Action::DETAIL);
    }

    private function filterListeTrash(SearchDto $searchDto, QueryBuilder $queryBuilder): QueryBuilder
    {
        return $this->filterTrash($searchDto, $queryBuilder);
    }

    private function filterListRefUser(QueryBuilder $queryBuilder, EntityDto $entityDto): QueryBuilder
    {
        // Ownership filter now handled by dedicated extension (OwnerRestrictionExtension)
        return $queryBuilder;
    }

    private function setLinkPublicAction(string $urlPublic): Action { return $this->linkActionFactory->createPublicAction($urlPublic); }

    private function setW3cValidatorAction(string $urlW3c): ?Action { return $this->linkActionFactory->createW3cAction($urlW3c); }

    /**
     * Internal helper to fetch a Doctrine repository with generics-like safety.
     */
    private function getDoctrineRepository(string $entity): ServiceEntityRepositoryLib
    {
        $em = $this->managerRegistry->getManagerForClass($entity);
        $repository = $em->getRepository($entity);
        \assert($repository instanceof ServiceEntityRepositoryLib);

        return $repository;
    }

    protected function isSuperAdmin(): bool
    {
        $user = $this->getUser();
        if (!is_object($user) || !method_exists($user, 'getRoles')) {
            return false;
        }

        return in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true);
    }
}
