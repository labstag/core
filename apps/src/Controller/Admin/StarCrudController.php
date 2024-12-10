<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use Labstag\Entity\Star;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

class StarCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->configureActionsBtn($actions);

        return $actions;
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['title' => 'ASC']
        );

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        unset($pageName);
        yield $this->addFieldID();
        yield TextField::new('title');
        yield TextField::new('language');
        yield TextField::new('repository');
        yield UrlField::new('url');
        yield TextEditorField::new('description')->hideOnIndex();
        yield TextField::new('license');
        yield IntegerField::new('stargazers');
        yield IntegerField::new('watchers');
        yield IntegerField::new('forks');
        yield $this->addFieldBoolean();
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $filters->add(ChoiceFilter::new('license')->setChoices($this->getallLicense()));
        $filters->add(ChoiceFilter::new('language')->setChoices($this->getallLanguages()));
        $filters->add(NumericFilter::new('stargazers'));
        $filters->add(NumericFilter::new('watchers'));
        $filters->add(NumericFilter::new('forks'));
        $filters->add(BooleanFilter::new('enable'));

        return $filters;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Star::class;
    }

    private function getallLanguages()
    {
        $repository = $this->getRepository();

        $all = $repository->findAllLanguage();

        $data = [];
        foreach ($all as $row) {
            $data[$row['language']] = $row['language'];
        }

        return $data;
    }

    private function getallLicense()
    {
        $repository = $this->getRepository();

        $all = $repository->findAllLicense();

        $data = [];
        foreach ($all as $row) {
            $data[$row['license']] = $row['license'];
        }

        return $data;
    }
}
