<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Labstag\Entity\GeoCode;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

class GeoCodeCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['stateName' => 'ASC']
        );

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        unset($pageName);
        yield CountryField::new('country_code');
        yield TextField::new('stateCode')->hideOnIndex();
        yield TextField::new('stateName');
        yield TextField::new('provinceCode')->hideOnIndex();
        yield TextField::new('provinceName');
        yield TextField::new('communityCode')->hideOnIndex();
        yield TextField::new('communityName');
        yield TextField::new('latitude');
        yield TextField::new('longitude');
        yield TextField::new('placeName');
        yield TextField::new('postalCode')->hideOnIndex();
        yield NumberField::new('accuracy');
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $filters->add(TextFilter::new('stateName'));
        $filters->add(TextFilter::new('provinceName'));
        $filters->add(TextFilter::new('communityName'));
        $filters->add(TextFilter::new('placeName'));
        $filters->add(TextFilter::new('postalCode'));

        return $filters;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return GeoCode::class;
    }
}
