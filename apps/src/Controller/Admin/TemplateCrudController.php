<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Template;
use Labstag\Field\WysiwygField;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class TemplateCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->setEditDetail($actions);
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield $this->addFieldID();
        yield $this->addFieldTitle();
        yield TextField::new('code', new TranslatableMessage('code'));
        yield WysiwygField::new('html', new TranslatableMessage('html'));
        yield TextareaField::new('text', new TranslatableMessage('text'));
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Template::class;
    }
}
