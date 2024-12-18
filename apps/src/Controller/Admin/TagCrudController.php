<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Labstag\Entity\Tag;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

abstract class TagCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        unset($pageName);

        return [
            $this->addFieldID(),
            $this->addFieldSlug(),
            $this->addFieldTitle(),
        ];
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Tag::class;
    }
}
