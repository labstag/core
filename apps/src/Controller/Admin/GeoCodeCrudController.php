<?php

namespace Labstag\Controller\Admin;

use Labstag\Entity\GeoCode;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

class GeoCodeCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public static function getEntityFqcn(): string
    {
        return GeoCode::class;
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
