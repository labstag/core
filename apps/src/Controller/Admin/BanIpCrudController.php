<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Controller\Admin\Abstract\AbstractCrudControllerLib;
use Labstag\Entity\BanIp;
use Labstag\Field\WysiwygField;
use Symfony\Component\Translation\TranslatableMessage;

class BanIpCrudController extends AbstractCrudControllerLib
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        unset($pageName);
        // Ensure all fields are inside a tab (EasyAdmin requires this once any tab is used elsewhere in the app)
        yield $this->addTabPrincipal();
        yield $this->crudFieldFactory->idField();
        yield $this->crudFieldFactory->booleanField('enable', (string) new TranslatableMessage('Enable'));
        yield TextField::new('InternetProtocol', new TranslatableMessage('IP'));
        yield WysiwygField::new('reason', new TranslatableMessage('Raison'));
        foreach ($this->crudFieldFactory->dateSet() as $field) {
            yield $field;
        }
    }

    public static function getEntityFqcn(): string
    {
        return BanIp::class;
    }
}
