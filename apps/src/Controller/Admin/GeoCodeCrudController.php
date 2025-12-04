<?php

namespace Labstag\Controller\Admin;

use Override;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Labstag\Entity\GeoCode;
use Labstag\Repository\GeoCodeRepository;
use Symfony\Component\Translation\TranslatableMessage;

class GeoCodeCrudController extends CrudControllerAbstract
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->setReadOnly(true);

        return $this->actionsFactory->show();
    }

    #[Override]
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

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());
        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                CountryField::new('countryCode', new TranslatableMessage('Country')),
                TextField::new('stateCode', new TranslatableMessage('State code'))->hideOnIndex(),
                TextField::new('stateName', new TranslatableMessage('State name')),
                TextField::new('provinceCode', new TranslatableMessage('Province code'))->hideOnIndex(),
                TextField::new('provinceName', new TranslatableMessage('Province name')),
                TextField::new('communityCode', new TranslatableMessage('Community code'))->hideOnIndex(),
                TextField::new('communityName', new TranslatableMessage('Community name')),
                TextField::new('latitude', new TranslatableMessage('Latitude')),
                TextField::new('longitude', new TranslatableMessage('Longitude')),
                TextField::new('placeName', new TranslatableMessage('Place')),
                TextField::new('postalCode', new TranslatableMessage('Postal code'))->hideOnIndex(),
                NumberField::new('accuracy', new TranslatableMessage('Accuracy')),
            ]
        );

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[Override]
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
        $repositoryAbstract = $this->getRepository();
        if (!$repositoryAbstract instanceof GeoCodeRepository) {
            return [];
        }

        $all = $repositoryAbstract->findAllData($type);

        $data = [];
        foreach ($all as $row) {
            $data[$row[$type]] = $row[$type];
        }

        return $data;
    }
}
