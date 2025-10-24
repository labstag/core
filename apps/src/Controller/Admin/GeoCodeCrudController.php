<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Labstag\Controller\Admin\Abstract\AbstractCrudControllerLib;
use Labstag\Entity\GeoCode;
use Labstag\Repository\GeoCodeRepository;
use Symfony\Component\Translation\TranslatableMessage;

class GeoCodeCrudController extends AbstractCrudControllerLib
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $actions->remove(Crud::PAGE_INDEX, Action::NEW);
        $actions->remove(Crud::PAGE_INDEX, Action::EDIT);
        $actions->remove(Crud::PAGE_INDEX, Action::DELETE);
        $actions->remove(Crud::PAGE_DETAIL, Action::EDIT);
        $actions->remove(Crud::PAGE_DETAIL, Action::DELETE);
        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);

        return $actions;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Geo Code'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Geo Codes'));
        $crud->setDefaultSort(
            ['stateName' => 'ASC']
        );

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        unset($pageName);
        yield CountryField::new('countryCode', new TranslatableMessage('Country'));
        yield TextField::new('stateCode', new TranslatableMessage('State code'))->hideOnIndex();
        yield TextField::new('stateName', new TranslatableMessage('State name'));
        yield TextField::new('provinceCode', new TranslatableMessage('Province code'))->hideOnIndex();
        yield TextField::new('provinceName', new TranslatableMessage('Province name'));
        yield TextField::new('communityCode', new TranslatableMessage('Community code'))->hideOnIndex();
        yield TextField::new('communityName', new TranslatableMessage('Community name'));
        yield TextField::new('latitude', new TranslatableMessage('Latitude'));
        yield TextField::new('longitude', new TranslatableMessage('Longitude'));
        yield TextField::new('placeName', new TranslatableMessage('Place'));
        yield TextField::new('postalCode', new TranslatableMessage('Postal code'))->hideOnIndex();
        yield NumberField::new('accuracy', new TranslatableMessage('Accuracy'));
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $filterFields = [
            'countryCode'   => new TranslatableMessage('Country'),
            'stateName'     => new TranslatableMessage('State'),
            'provinceName'  => new TranslatableMessage('Province'),
            'communityName' => new TranslatableMessage('Community'),
        ];
        foreach ($filterFields as $filterField => $label) {
            $data = $this->getAllData($filterField);
            if ([] === $data) {
                continue;
            }

            $filters->add(ChoiceFilter::new($filterField, $label)->setChoices($data));
        }

        $filters->add(TextFilter::new('placeName', new TranslatableMessage('Place')));
        $filters->add(TextFilter::new('postalCode', new TranslatableMessage('Postal code')));

        return $filters;
    }

    public static function getEntityFqcn(): string
    {
        return GeoCode::class;
    }

    /**
     * @return mixed[]
     */
    private function getAllData(string $type): array
    {
        $serviceEntityRepositoryLib = $this->getRepository();
        if (!$serviceEntityRepositoryLib instanceof GeoCodeRepository) {
            return [];
        }

        $all = $serviceEntityRepositoryLib->findAllData($type);

        $data = [];
        foreach ($all as $row) {
            $data[$row[$type]] = $row[$type];
        }

        return $data;
    }
}
