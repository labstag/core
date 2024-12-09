<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Labstag\Entity\Star;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
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

    public static function getEntityFqcn(): string
    {
        return Star::class;
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $filters->add(TextFilter::new('license'));
        $filters->add(NumericFilter::new('stargazers'));
        $filters->add(NumericFilter::new('watchers'));
        $filters->add(NumericFilter::new('forks'));
        $filters->add(BooleanFilter::new('enable'));

        return $filters;
    }

    public function configureFields(string $pageName): iterable
    {
        yield $this->addFieldID();
        yield TextField::new('title');
        yield TextField::new('repository');
        yield UrlField::new('url');
        yield TextEditorField::new('description')->hideOnIndex();
        yield TextField::new('license');
        yield IntegerField::new('stargazers');
        yield IntegerField::new('watchers');
        yield IntegerField::new('forks');
        yield $this->addFieldBoolean();
    }
}
