<?php

namespace Labstag\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Labstag\Entity\Block;
use Labstag\Filter\DiscriminatorTypeFilter;
use Labstag\Repository\BlockRepository;
use LogicException;
use Override;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;

class BlockCrudController extends CrudControllerAbstract
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->remove(Crud::PAGE_INDEX, Action::NEW);

        $action = Action::new('positionBlock', new TranslatableMessage('Change Position'), 'fas fa-arrows-alt');
        $action->renderAsLink();
        $action->linkToCrudAction('positionBlock');
        $action->createAsGlobalAction();

        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);

        $action = Action::new('showModalBlock', new TranslatableMessage('New block'));
        $action->linkToCrudAction('showModalBlock');
        $action->setHtmlAttributes([
                'data-action' => 'show-modal',
            ]);
        $action->createAsGlobalAction();

        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);

        return $this->actionsFactory->show();
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(
            function ($block, ?string $pageName): TranslatableMessage {
                unset($pageName);
                if (is_null($block)) {
                    $request = $this->requestStack->getCurrentRequest();

                    $type = $request->query->get('type');
                    if (is_null($type)) {
                        return new TranslatableMessage('Block');
                    }

                    $classe = $this->blockService->getByCode($type);
                    if (!is_object($classe)) {
                        return new TranslatableMessage('Block');
                    }

                    $name = $classe->getName();

                    return new TranslatableMessage(
                        'Block %name%',
                        [
                            '%name%' => $name,
                        ]
                    );
                }

                $name = $this->blockService->getName($block);

                return new TranslatableMessage(
                    'Block %name%',
                    [
                        '%name%' => $name,
                    ]
                );
            }
        );
        $crud->setEntityLabelInPlural(new TranslatableMessage('Blocks'));
        $crud->setDefaultSort([
                'title' => 'ASC',
            ]);

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());
        $currentEntity = $this->getContext()
            ->getEntity()
            ->getInstance();

        $regionField = ChoiceField::new('region', new TranslatableMessage('Region'));
        $regionField->setChoices($this->blockService->getRegions());

        $numberField = NumberField::new('position', new TranslatableMessage('Position'));
        $numberField->hideOnForm();

        $fields      = [
            $this->crudFieldFactory->booleanField('enable', new TranslatableMessage('Enable')),
            $this->crudFieldFactory->titleField(),
            $regionField,
            $numberField,
        ];

        $this->crudFieldFactory->addFieldsToTab('principal', $fields);

        $this->crudFieldFactory->setTabOther();
        $this->crudFieldFactory->addFieldsToTab('other', $this->blockService->getFields($currentEntity, $pageName));

        $this->crudFieldFactory->setTabConfig();

        $choiceField = ChoiceField::new('roles', new TranslatableMessage('Roles'));
        $choiceField->hideOnIndex();
        $choiceField->allowMultipleChoices();
        $choiceField->setChoices($this->userService->getRoles());

        $textareaField = TextareaField::new('pages', new TranslatableMessage('Pages'));
        $textareaField->setHelp(new TranslatableMessage('Separate pages with commas'));
        $textareaField->hideOnIndex();

        $requestPathField = ChoiceField::new('request_path', new TranslatableMessage('Request Path'));
        $requestPathField->renderExpanded();
        $requestPathField->hideOnIndex();
        $requestPathField->setRequired(true);
        $requestPathField->setChoices(
            [
                $this->translator->trans(new TranslatableMessage('Show for listed pages')) => '0',
                $this->translator->trans(new TranslatableMessage('Hide for listed pages')) => '1',
            ]
        );
        $this->crudFieldFactory->addFieldsToTab(
            'config',
            [
                $choiceField,
                $textareaField,
                $requestPathField,
                TextField::new('classes', new TranslatableMessage('classes'))->hideOnIndex(),
            ]
        );

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterEnable($filters);
        $types = $this->blockService->getAll(null);
        if ([] == $types) {
            return $filters;
        }

        $discriminatorTypeFilter = DiscriminatorTypeFilter::new('type', new TranslatableMessage('Type'));
        $discriminatorTypeFilter->setBlockService($this->blockService);
        $discriminatorTypeFilter->setChoices(array_merge([
                    '' => '',
                ], $types));

        $filters->add($discriminatorTypeFilter);

        return $filters;
    }

    #[Override]
    public function createEntity(string $entityFqcn): object
    {
        unset($entityFqcn);
        $request = $this->requestStack->getCurrentRequest();

        $type   = $request->query->get('type');
        $classe = $this->blockService->getClasseByCode($type);
        if (is_null($type)) {
            throw new LogicException('Impossible de créer un Block sans type.');
        }

        return new $classe();
    }

    #[Override]
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fieldCollection,
        FilterCollection $filterCollection,
    ): QueryBuilder
    {
        // Use the parent query builder so EasyAdmin can apply search and filters (including DiscriminatorTypeFilter)
        $queryBuilder = parent::createIndexQueryBuilder(
            $searchDto,
            $entityDto,
            $fieldCollection,
            $filterCollection
        );

        $repositoryAbstract = $this->getRepository();
        $methods            = get_class_methods($repositoryAbstract);
        if (in_array('findAllOrderedByRegion', $methods)) {
            $repositoryAbstract->findAllOrderedByRegion($queryBuilder);
        }

        return $queryBuilder;
    }

    /**
     * @return FormBuilderInterface<mixed>
     */
    #[Override]
    public function createNewFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $keyValueStore,
        AdminContext $adminContext,
    ): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $keyValueStore, $adminContext);

        return $formBuilder->addEventListener(FormEvents::SUBMIT, $this->setPosition());
    }

    public static function getEntityFqcn(): string
    {
        return Block::class;
    }

    public function newBlock(): Response
    {
        $blocks = $this->blockService->getAll(null);

        return $this->render('admin/block/new.html.twig', [
                'controller' => static::class,
                'blocks'     => $blocks,
            ]);
    }

    public function positionBlock(Request $request): RedirectResponse|Response
    {
        $repositoryAbstract              = $this->getRepository();
        if (!$repositoryAbstract instanceof BlockRepository) {
            throw new Exception('findAllOrderedByRegion not found');
        }

        $queryBuilder = $repositoryAbstract->createQueryBuilder('b');
        $repositoryAbstract->findAllOrderedByRegion($queryBuilder);
        $query        = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'block-position');

        $blocks    = $query->getResult();
        $generator = $this->container->get(AdminUrlGenerator::class);
        if ($request->isMethod('POST')) {
            $allTypes = $this->blockService->getRegions();
            foreach ($allTypes as $allType) {
                $data = $request->get($allType);
                if (is_null($data)) {
                    continue;
                }

                foreach ($data as $id => $position) {
                    $entity = $repositoryAbstract->find($id);
                    if (!$entity instanceof Block) {
                        continue;
                    }

                    $entity->setPosition($position);
                    $repositoryAbstract->persist($entity);
                }
            }

            $repositoryAbstract->flush();
            $this->addFlash('success', new TranslatableMessage('Position updated'));

            $url = $generator->setController(static::class)->setAction(Action::INDEX)->generateUrl();

            return $this->redirect($url);
        }

        return $this->render('admin/block/order.html.twig', [
                'blocks' => $blocks,
            ]);
    }

    public function showModalBlock(): Response
    {
        $blocks = $this->blockService->getAll(null);

        return $this->render('admin/block/new.html.twig', [
                'controller' => static::class,
                'blocks'     => $blocks,
            ]);
    }

    private function setPosition(): callable
    {
        return function ($event): void
        {
            $form = $event->getForm();
            if (!$form->isSubmitted()) {
                return;
            }

            $data                            = $event->getData();
            $repositoryAbstract              = $this->getRepository();
            $region                          = $form->get('region')
                ->getData();
            if (is_null($region) || !$repositoryAbstract instanceof BlockRepository) {
                return;
            }

            $maxPosition = $repositoryAbstract->getMaxPositionByRegion($region);
            if (is_null($maxPosition)) {
                $maxPosition = 0;
            }

            ++$maxPosition;
            $data->setPosition($maxPosition);
        };
    }
}
