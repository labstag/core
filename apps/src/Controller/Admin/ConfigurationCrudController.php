<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Labstag\Entity\Configuration;
use Labstag\Field\WysiwygField;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class ConfigurationCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $actions->remove(Crud::PAGE_INDEX, Action::NEW);
        $actions->remove(Crud::PAGE_INDEX, Action::DELETE);
        $actions->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN);

        return $actions;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield $this->addTabPrincipal();
        yield TextField::new('titleFormat', new TranslatableMessage('Title format'));
        yield TextField::new('name', new TranslatableMessage('Site name'));
        yield EmailField::new('email', new TranslatableMessage('Email'));
        yield UrlField::new('url', new TranslatableMessage('Url'));
        yield EmailField::new('noreply', new TranslatableMessage('Email no-reply'));
        yield WysiwygField::new('Copyright', new TranslatableMessage('Copyright'));
        yield BooleanField::new('userShow', new TranslatableMessage('Show user'));
        yield BooleanField::new('userLink', new TranslatableMessage('Link user'));
        yield FormField::addTab(new TranslatableMessage('Security'));
        yield BooleanField::new('disableEmptyAgent', new TranslatableMessage('Disable empty agent'));
        yield FormField::addTab(new TranslatableMessage('Sitemap'));
        yield BooleanField::new('sitemapPosts', new TranslatableMessage('Show posts'));
        yield BooleanField::new('sitemapStory', new TranslatableMessage('Show story'));
        yield FormField::addTab(new TranslatableMessage('Medias'));
        yield $this->addFieldImageUpload('logo', $pageName, new TranslatableMessage('Logo'));
        yield $this->addFieldImageUpload('placeholder', $pageName, new TranslatableMessage('Placeholder'));
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Configuration::class;
    }
}
