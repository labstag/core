<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Labstag\Controller\Admin\Abstract\AbstractCrudControllerLib;
use Labstag\Entity\Season;
use Labstag\Field\WysiwygField;
use Symfony\Component\Translation\TranslatableMessage;

class SeasonCrudController extends AbstractCrudControllerLib
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $actions->add(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE);
        $this->setEditDetail($actions);
        $this->configureActionsTrash($actions);
        $this->configureActionsUpdateImage();

        return $actions;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Season'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Seasons'));
        $crud->setDefaultSort(
            [
                'refserie' => 'ASC',
                'number'   => 'ASC',
            ]
        );

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield $this->addTabPrincipal();
        yield $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn());
        yield TextField::new('tmdb', new TranslatableMessage('Tmdb'))->hideOnIndex();
        yield AssociationField::new('refserie', new TranslatableMessage('Serie'));
        yield IntegerField::new('number', new TranslatableMessage('Number'));
        yield DateField::new('air_date', new TranslatableMessage('Air date'));
        yield from [WysiwygField::new('overview', new TranslatableMessage('Overview'))->hideOnIndex()];
        foreach ($this->crudFieldFactory->dateSet() as $field) {
            yield $field;
        }
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterEnable($filters);
        $filters->add(EntityFilter::new('refserie', new TranslatableMessage('Serie')));

        return $filters;
    }

    public static function getEntityFqcn(): string
    {
        return Season::class;
    }

    private function configureActionsUpdateImage(): void
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $request->query->get('action', null);
    }
}
