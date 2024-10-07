<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\User;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;

class UserCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('username');
        yield EmailField::new('email');
        yield $this->addFieldBoolean();
        $choiceField = ChoiceField::new('roles');
        $choiceField->allowMultipleChoices();
        $choiceField->setChoices(
            [
                'User'        => 'ROLE_USER',
                'Admin'       => 'ROLE_ADMIN',
                'Super Admin' => 'ROLE_SUPER_ADMIN',
            ]
        );
        yield $choiceField;
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
        yield $this->addFieldImageUpload('avatar', $pageName);
        yield CollectionField::new('histories')->onlyOnDetail();
        yield CollectionField::new('editos')->onlyOnDetail()->formatValue(
            fn ($entity) => count($entity)
        );

        $tab = [
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
    public function createEditFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $keyValueStore,
        AdminContext $adminContext
    ): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $keyValueStore, $adminContext);

        return $this->addPasswordEventListener($formBuilder);
    }

    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $user = new $entityFqcn();
        $this->workflowService->init($user);

        return $user;
    }

    #[Override]
    public function createNewFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $keyValueStore,
        AdminContext $adminContext
    ): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $keyValueStore, $adminContext);

        return $this->addPasswordEventListener($formBuilder);
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    private function addPasswordEventListener(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        return $formBuilder->addEventListener(FormEvents::POST_SUBMIT, $this->hashPassword());
    }

    private function hashPassword()
    {
        return function ($event)
        {
            $form = $event->getForm();
            if (!$form->isValid()) {
                return;
            }

            $password = $form->get('password')->getData();
            if (null === $password) {
                return;
            }

            $hash = $this->userPasswordHasher->hashPassword($event->getData(), $password);
            $form->getData()->setPassword($hash);
        };
    }
}
