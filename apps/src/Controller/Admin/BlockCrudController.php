<?php

namespace Labstag\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Labstag\Entity\Block;
use Labstag\Form\Paragraphs\BlockType;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;

class BlockCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $action = Action::new('positionBlock', 'Changer la position', 'fas fa-arrows-alt');
        $action->displayAsLink();
        $action->linkToCrudAction('positionBlock');
        $action->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $action);

        return $actions;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        yield FormField::addTab('Principal');
        yield $this->addFieldID();
        yield TextField::new('title');
        yield ChoiceField::new('region')->setChoices($this->blockService->getRegions());
        $numberField = NumberField::new('position')->hideOnForm();
        yield $numberField;
        $allTypes = array_flip($this->blockService->getAll(null));
        yield $this->getChoiceType($pageName, $allTypes);
        yield $this->addFieldBoolean();
        $fields = array_merge(
            $this->addFieldParagraphs($pageName, BlockType::class),
            $this->paragraphService->getFieldsCrudEA($currentEntity)
        );
        foreach ($fields as $field) {
            yield $field;
        }
    }

    #[Override]
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fieldCollection,
        FilterCollection $filterCollection
    ): QueryBuilder
    {
        unset($searchDto, $entityDto, $fieldCollection, $filterCollection);
        $repo = $this->getRepository();

        return $repo->findAllOrderedByRegion();
    }

    #[Override]
    public function createNewFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $keyValueStore,
        AdminContext $adminContext
    ): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $keyValueStore, $adminContext);

        return $formBuilder->addEventListener(
            FormEvents::SUBMIT,
            $this->setPosition()
        );
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Block::class;
    }

    public function positionBlock(AdminContext $adminContext)
    {
        $request    = $adminContext->getRequest();
        $repository = $this->getRepository();
        $query      = $repository->findAllOrderedByRegion();
        $blocks     = $query->getQuery()->getResult();
        $generator  = $this->container->get(AdminUrlGenerator::class);

        if ($request->isMethod('POST')) {
            $allTypes = $this->blockService->getRegions();
            foreach ($allTypes as $allType) {
                $data = $request->get($allType);
                if (is_null($data)) {
                    continue;
                }

                foreach ($data as $id => $position) {
                    $entity = $repository->find($id);
                    if (!$entity instanceof Block) {
                        continue;
                    }

                    $entity->setPosition($position);
                    $repository->persist($entity);
                }
            }

            $repository->flush();
            $this->addFlash('success', 'Position mise à jour');

            $url = $generator->setController(static::class)->setAction(Action::INDEX)->generateUrl();

            return $this->redirect($url);
        }

        return $this->render(
            'admin/block/order.html.twig',
            ['blocks' => $blocks]
        );
    }

    private function getChoiceType($pageName, $allTypes)
    {
        if ('new' === $pageName) {
            $field = ChoiceField::new('type');
            $field->setChoices(array_flip($allTypes));

            return $field;
        }

        $field = TextField::new('type');
        $field->formatValue(
            static fn ($value) => $allTypes[$value] ?? null
        );
        $field->setDisabled(true);

        return $field;
    }

    private function setPosition()
    {
        return function ($event)
        {
            $form = $event->getForm();
            if (!$form->isValid()) {
                return;
            }

            $data = $event->getData();

            $repository = $this->getRepository();

            $region = $form->get('region')->getData();
            if (is_null($region)) {
                return;
            }

            $maxPosition = $repository->getMaxPositionByRegion($region);
            if (is_null($maxPosition)) {
                $maxPosition = 0;
            }

            ++$maxPosition;
            $data->setPosition($maxPosition);
        };
    }
}
