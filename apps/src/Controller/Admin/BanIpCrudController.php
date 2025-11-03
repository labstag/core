<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\BanIp;
use Labstag\Field\WysiwygField;
use Symfony\Component\Translation\TranslatableMessage;

class BanIpCrudController extends CrudControllerAbstract
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
        $crud->setEntityLabelInSingular(new TranslatableMessage('Ban IP'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Ban IP'));
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal(self::getEntityFqcn());
        $fields = [
            $this->crudFieldFactory->booleanField('enable', (string) new TranslatableMessage('Enable')),
            TextField::new('InternetProtocol', new TranslatableMessage('IP')),
            WysiwygField::new('reason', new TranslatableMessage('Raison')),
        ];
        $this->crudFieldFactory->addFieldsToTab('principal', $fields);
        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    public static function getEntityFqcn(): string
    {
        return BanIp::class;
    }
}
