<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use Labstag\Entity\Edito;
use Symfony\Component\Translation\TranslatableMessage;

class EditoCrudController extends CrudControllerAbstract
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);

        return $this->actionsFactory->show();
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Edito'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Editos'));
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal(self::getEntityFqcn());
        $fields   = [
            $this->crudFieldFactory->booleanField('enable', (string) new TranslatableMessage('Enable')),
            $this->crudFieldFactory->titleField(),
            $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn()),
        ];
        $this->crudFieldFactory->addFieldsToTab('principal', $fields);

        $this->crudFieldFactory->setTabParagraphs($pageName);

        $this->crudFieldFactory->setTabUser($this->isSuperAdmin());

        $this->crudFieldFactory->setTabWorkflow();

        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterRefUserFor($filters, self::getEntityFqcn());
        $this->crudFieldFactory->addFilterEnable($filters);

        return $filters;
    }

    public static function getEntityFqcn(): string
    {
        return Edito::class;
    }
}
