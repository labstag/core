<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Meta;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

class MetaCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public static function getEntityFqcn(): string
    {
        return Meta::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
