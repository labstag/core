<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\User;
use Override;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Translation\TranslatableMessage;

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
        yield TextField::new('username', new TranslatableMessage('Username'));
        yield EmailField::new('email', new TranslatableMessage('Email'));
        $textField = TextField::new('password', new TranslatableMessage('Password'));
        $textField->setFormType(RepeatedType::class);
        $textField->setFormTypeOptions(
            [
                'type'           => PasswordType::class,
                'first_options'  => [
                    'label' => new TranslatableMessage('Password'),
                    'attr'  => ['autocomplete' => 'new-password'],
                ],
                'second_options' => [
                    'label' => new TranslatableMessage('Repeat Password'),
                    'attr'  => ['autocomplete' => 'new-password'],
                ],
                'mapped'         => false,
            ]
        );
        $textField->setRequired(Crud::PAGE_NEW === $pageName);
        $textField->onlyOnForms();
        yield $textField;
        $choiceField = ChoiceField::new('language', new TranslatableMessage('Language'));
        $choiceField->setChoices($this->userService->getLanguagesForChoices());
        yield $choiceField;
        yield $this->addFieldImageUpload('avatar', $pageName);
        yield CollectionField::new('stories', new TranslatableMessage('Histories'))->onlyOnDetail();
        yield CollectionField::new('editos', new TranslatableMessage('Editos'))->onlyOnDetail()->formatValue(
            fn ($entity) => count($entity)
        );

        $tab = [
            'editos' => new TranslatableMessage('Editos'),
            'memos'  => new TranslatableMessage('memos'),
            'pages'  => new TranslatableMessage('pages'),
            'posts'  => new TranslatableMessage('posts'),
        ];
        foreach ($tab as $key => $label) {
            $collectionField = CollectionField::new($key, $label);
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
