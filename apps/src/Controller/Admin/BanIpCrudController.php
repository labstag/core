<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\BanIp;
use Labstag\Field\WysiwygField;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class BanIpCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        unset($pageName);
        yield $this->addFieldID();
        yield $this->addFieldBoolean('enable', new TranslatableMessage('Enable'));
        yield TextField::new('InternetProtocol', new TranslatableMessage('IP'));
        $wysiwygField = WysiwygField::new('reason', new TranslatableMessage('Raison'));
        yield $wysiwygField;
        $date = $this->addTabDate();
        foreach ($date as $field) {
            yield $field;
        }
    }

    public static function getEntityFqcn(): string
    {
        return BanIp::class;
    }
}
