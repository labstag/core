<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use Labstag\Entity\Star;
use Labstag\Lib\AbstractCrudControllerLib;
use Labstag\Repository\StarRepository;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class StarCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->configureActionsBtn($actions);
        $actions->remove(Crud::PAGE_INDEX, Action::EDIT);
        $actions->remove(Crud::PAGE_DETAIL, Action::EDIT);

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
        yield $this->addTabPrincipal();
        unset($pageName);
        yield $this->addFieldID();
        yield $this->addFieldTitle();
        yield TextField::new('language', new TranslatableMessage('Language'));
        yield TextField::new('repository', new TranslatableMessage('Repository'))->hideOnIndex();
        yield UrlField::new('url', new TranslatableMessage('Url'));
        yield TextEditorField::new('description', new TranslatableMessage('Description'))->hideOnIndex();
        yield TextField::new('license', new TranslatableMessage('License'));
        yield IntegerField::new('stargazers', new TranslatableMessage('Stargazers'));
        yield IntegerField::new('watchers', new TranslatableMessage('Watchers'));
        yield IntegerField::new('forks', new TranslatableMessage('Forks'));
        yield $this->addFieldBoolean('enable', new TranslatableMessage('Enable'));
        
        $date = $this->addTabDate();
        foreach ($date as $field) {
            yield $field;
        }
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $licences = $this->getallData('license');
        if (0 != count($licences)) {
            $filters->add(ChoiceFilter::new('license', new TranslatableMessage('License'))->setChoices($licences));
        }

        $languages = $this->getallData('language');
        if (0 != count($languages)) {
            $filters->add(ChoiceFilter::new('language', new TranslatableMessage('Language'))->setChoices($languages));
        }

        $filters->add(NumericFilter::new('stargazers', new TranslatableMessage('stargazers')));
        $filters->add(NumericFilter::new('watchers', new TranslatableMessage('watchers')));
        $filters->add(NumericFilter::new('forks', new TranslatableMessage('forks')));
        $this->addFilterEnable($filters);

        return $filters;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Star::class;
    }

    /**
     * @return mixed[]
     */
    private function getAllData(string $type): array
    {
        $serviceEntityRepositoryLib = $this->getRepository();
        if (!$serviceEntityRepositoryLib instanceof StarRepository) {
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
