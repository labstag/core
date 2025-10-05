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
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Labstag\Controller\Admin\Abstract\AbstractCrudControllerLib;
use Labstag\Entity\Block;
use Labstag\Repository\BlockRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;

class BlockCrudController extends AbstractCrudControllerLib
{
    /**
     * @return mixed[]
     */
    public function addFieldParagraphsForBlock(?Block $block, string $pageName): array
    {
        if ('edit' === $pageName && $block instanceof Block) {
            if (in_array($block->getType(), ['paragraphs', 'content'])) {
                return $this->crudFieldFactory->paragraphFields($pageName);
            }

            return [];
        }

        return $this->crudFieldFactory->paragraphFields($pageName);
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $action = Action::new('positionBlock', new TranslatableMessage('Change Position'), 'fas fa-arrows-alt');
        $action->displayAsLink();
        $action->linkToCrudAction('positionBlock');
        $action->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $action);

        return $actions;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Block'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Blocks'));
        $crud->setDefaultSort(
            ['title' => 'ASC']
        );

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        yield $this->addTabPrincipal();
        foreach ($this->crudFieldFactory->baseIdentitySet(
            'block',
            $pageName,
            self::getEntityFqcn(),
            withSlug: false,
            withImage: false
        ) as $field) {
            yield $field;
        }

        yield ChoiceField::new('region', new TranslatableMessage('Region'))->setChoices(
            $this->blockService->getRegions()
        );
        $numberField = NumberField::new('position', new TranslatableMessage('Position'))->hideOnForm();
        yield $numberField;
        $allTypes = array_flip($this->blockService->getAll(null));
        yield $this->getChoiceType($pageName, $allTypes);
        $fields = array_merge(
            $this->addFieldParagraphsForBlock($currentEntity, $pageName),
            $this->blockService->getFields($currentEntity, $pageName)
        );
        foreach ($fields as $field) {
            yield $field;
        }

        yield FormField::addTab(new TranslatableMessage('Config'));
        $choiceField = ChoiceField::new('roles', new TranslatableMessage('Roles'));
        $choiceField->hideOnIndex();
        $choiceField->allowMultipleChoices();
        $choiceField->setChoices($this->userService->getRoles());
        yield $choiceField;
        $textareaField = TextareaField::new('pages', new TranslatableMessage('Pages'));
        $textareaField->setHelp(new TranslatableMessage('Separate pages with commas'));
        $textareaField->hideOnIndex();
        yield $textareaField;
        $requestPathField = ChoiceField::new('request_path', new TranslatableMessage('Request Path'));
        $requestPathField->renderExpanded();
        $requestPathField->hideOnIndex();
        $requestPathField->setRequired(true);
        $requestPathField->setChoices(
            [
                (string) new TranslatableMessage('Show for listed pages') => '0',
                (string) new TranslatableMessage('Hide for listed pages') => '1',
            ]
        );
        yield $requestPathField;
        yield TextField::new('classes', new TranslatableMessage('classes'))->hideOnIndex();
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterEnable($filters);

        return $filters;
    }

    #[\Override]
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fieldCollection,
        FilterCollection $filterCollection,
    ): QueryBuilder
    {
        unset($searchDto, $entityDto, $fieldCollection, $filterCollection);
        $serviceEntityRepositoryLib = $this->getRepository();
        if (!$serviceEntityRepositoryLib instanceof BlockRepository) {
            throw new Exception('findAllOrderedByRegion not found');
        }

        return $serviceEntityRepositoryLib->findAllOrderedByRegion();
    }

    /**
     * @return FormBuilderInterface<mixed>
     */
    #[\Override]
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

    public function positionBlock(AdminContext $adminContext): RedirectResponse|Response
    {
        $request                    = $adminContext->getRequest();
        $serviceEntityRepositoryLib = $this->getRepository();
        if (!$serviceEntityRepositoryLib instanceof BlockRepository) {
            throw new Exception('findAllOrderedByRegion not found');
        }

        $queryBuilder = $serviceEntityRepositoryLib->findAllOrderedByRegion();
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
                    $entity = $serviceEntityRepositoryLib->find($id);
                    if (!$entity instanceof Block) {
                        continue;
                    }

                    $entity->setPosition($position);
                    $serviceEntityRepositoryLib->persist($entity);
                }
            }

            $serviceEntityRepositoryLib->flush();
            $this->addFlash('success', new TranslatableMessage('Position updated'));

            $url = $generator->setController(static::class)->setAction(Action::INDEX)->generateUrl();

            return $this->redirect($url);
        }

        return $this->render(
            'admin/block/order.html.twig',
            ['blocks' => $blocks]
        );
    }

    /**
     * @param mixed[] $allTypes
     */
    private function getChoiceType(string $pageName, array $allTypes): ChoiceField|TextField
    {
        if ('new' === $pageName) {
            $field = ChoiceField::new('type', new TranslatableMessage('Type'));
            $field->setChoices(array_flip($allTypes));

            return $field;
        }

        $field = TextField::new('type', new TranslatableMessage('Type'));
        $field->formatValue(static fn ($value) => $allTypes[$value] ?? null);
        $field->setDisabled(true);

        return $field;
    }

    private function setPosition(): callable
    {
        return function ($event): void
        {
            $form = $event->getForm();
            if (!$form->isSubmitted()) {
                return;
            }

            $data                       = $event->getData();
            $serviceEntityRepositoryLib = $this->getRepository();
            $region                     = $form->get('region')->getData();
            if (is_null($region) || !$serviceEntityRepositoryLib instanceof BlockRepository) {
                return;
            }

            $maxPosition = $serviceEntityRepositoryLib->getMaxPositionByRegion($region);
            if (is_null($maxPosition)) {
                $maxPosition = 0;
            }

            ++$maxPosition;
            $data->setPosition($maxPosition);
        };
    }
}
