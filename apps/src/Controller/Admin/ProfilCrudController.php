<?php

namespace Labstag\Controller\Admin;

use Override;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class ProfilCrudController extends UserCrudController
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $actions->remove(Crud::PAGE_INDEX, Action::NEW);
        $actions->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN);

        return $actions;
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud->setPageTitle(Crud::PAGE_EDIT, 'Mon profil');
        $crud->setEntityPermission('ROLE_SUPER_ADMIN');
        $crud->setFormThemes(['admin/form.html.twig']);

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('username');
        yield EmailField::new('email');
        $textField = TextField::new('password');
        $textField->setFormType(RepeatedType::class);
        $textField->setFormTypeOptions(
            [
                'type'           => PasswordType::class,
                'first_options'  => [
                    'label' => 'Mot de passe',
                    'attr'  => ['autocomplete' => 'new-password'],
                ],
                'second_options' => [
                    'label' => 'Répéter le mot de passe',
                    'attr'  => ['autocomplete' => 'new-password'],
                ],
                'mapped'         => false,
            ]
        );
        $textField->setRequired(Crud::PAGE_NEW === $pageName);
        $textField->onlyOnForms();
        yield $textField;
        $choiceField = ChoiceField::new('language');
        $choiceField->setChoices($this->userService->getLanguagesForChoices());
        yield $choiceField;
        yield $this->addFieldImageUpload('avatar', $pageName);
        yield CollectionField::new('histories')->onlyOnDetail();
        yield CollectionField::new('editos')->onlyOnDetail()->formatValue(
            fn ($entity) => count($entity)
        );

        $tab = [
            'editos',
            'memos',
            'pages',
            'posts',
        ];
        foreach ($tab as $key) {
            $collectionField = CollectionField::new($key);
            $collectionField->onlyOnDetail();
            $collectionField->formatValue(fn ($value) => count($value));
            yield $collectionField;
        }
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return User::class;
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
