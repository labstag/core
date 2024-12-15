<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Override;
use Labstag\Entity\Configuration;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Lib\AbstractCrudControllerLib;
use Symfony\Component\Translation\TranslatableMessage;

class ConfigurationCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public static function getEntityFqcn(): string
    {
        return Configuration::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->remove(Crud::PAGE_INDEX, Action::NEW);
        $actions->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN);

        return $actions;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield $this->addTabPrincipal();
        yield TextField::new('title_format', new TranslatableMessage('Title format'));
        yield TextField::new('site_name', new TranslatableMessage('Site name'));
        yield BooleanField::new('user_show', new TranslatableMessage('Show user'));
        yield BooleanField::new('user_link', new TranslatableMessage('Link user'));
        yield FormField::addTab(new TranslatableMessage('Medias'));
        yield $this->addFieldImageUpload('logo', $pageName, new TranslatableMessage('Logo'));
        yield $this->addFieldImageUpload('placeholder', $pageName, new TranslatableMessage('placeholder'));
        yield $this->addFieldImageUpload('favicon', $pageName, new TranslatableMessage('favicon'));
    }
}
