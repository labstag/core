<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Edito;
use Labstag\Entity\Meta;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

class EditoCrudController extends AbstractCrudControllerLib
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
        yield $this->addFieldID();
        yield $this->addFieldSlug();
        yield $this->addFieldBoolean();
        yield TextField::new('title');
        yield $this->addFieldRefUser();
    }

    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $edito = new Edito();
        $meta  = new Meta();
        $edito->addMeta($meta);

        return $edito;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Edito::class;
    }
}
