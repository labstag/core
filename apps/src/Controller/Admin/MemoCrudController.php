<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Memo;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

class MemoCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public static function getEntityFqcn(): string
    {
        return Memo::class;
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
