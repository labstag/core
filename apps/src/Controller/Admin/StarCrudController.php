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
use Labstag\Repository\StarRepository;
use Symfony\Component\Translation\TranslatableMessage;

class StarCrudController extends CrudControllerAbstract
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->configureActionsBtn($actions);
        $actions->remove(Crud::PAGE_INDEX, Action::EDIT);
        $actions->remove(Crud::PAGE_DETAIL, Action::EDIT);

        return $actions;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Star'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Stars'));
        $crud->setDefaultSort(
            ['title' => 'ASC']
        );

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal();
        $textField = TextField::new('repository', new TranslatableMessage('Repository'));
        $textField->hideOnIndex();

        $textEditorField = TextEditorField::new('description', new TranslatableMessage('Description'));
        $textEditorField->hideOnIndex();

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->idField(),
                $this->crudFieldFactory->booleanField('enable', (string) new TranslatableMessage('Enable')),
                $this->crudFieldFactory->titleField(),
                $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn()),
                TextField::new('language', new TranslatableMessage('Language')),
                $textField,
                UrlField::new('url', new TranslatableMessage('Url')),
                $textEditorField,
                TextField::new('license', new TranslatableMessage('License')),
                IntegerField::new('stargazers', new TranslatableMessage('Stargazers')),
                IntegerField::new('watchers', new TranslatableMessage('Watchers')),
                IntegerField::new('forks', new TranslatableMessage('Forks')),
            ]
        );

        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields();
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $licences = $this->getallData('license');
        if ([] !== $licences) {
            $filters->add(ChoiceFilter::new('license', new TranslatableMessage('License'))->setChoices($licences));
        }

        $languages = $this->getallData('language');
        if ([] !== $languages) {
            $filters->add(ChoiceFilter::new('language', new TranslatableMessage('Language'))->setChoices($languages));
        }

        $filters->add(NumericFilter::new('stargazers', new TranslatableMessage('stargazers')));
        $filters->add(NumericFilter::new('watchers', new TranslatableMessage('watchers')));
        $filters->add(NumericFilter::new('forks', new TranslatableMessage('forks')));

        $this->crudFieldFactory->addFilterEnable($filters);

        return $filters;
    }

    public static function getEntityFqcn(): string
    {
        return Star::class;
    }

    /**
     * @return mixed[]
     */
    private function getAllData(string $type): array
    {
        $serviceEntityRepositoryAbstract = $this->getRepository();
        if (!$serviceEntityRepositoryAbstract instanceof StarRepository) {
            return [];
        }

        $all = $serviceEntityRepositoryAbstract->findAllData($type);

        $data = [];
        foreach ($all as $row) {
            $data[$row[$type]] = $row[$type];
        }

        return $data;
    }
}
