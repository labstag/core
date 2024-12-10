<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
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
        yield CountryField::new('countryCode');
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
        $filterFields = ['countryCode', 'stateName', 'provinceName', 'communityName'];
        foreach ($filterFields as $field) {
            $filters->add(ChoiceFilter::new($field)->setChoices($this->getAllData($field)));
        }

        $filters->add(TextFilter::new('placeName'));
        $filters->add(TextFilter::new('postalCode'));

        return $filters;
    }

    private function getAllData($type)
    {
        $repository = $this->getRepository();

        $all = $repository->findAllData($type);

        $data = [];
        foreach ($all as $row) {
            $data[$row[$type]] = $row[$type];
        }

        return $data;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return GeoCode::class;
    }
}
