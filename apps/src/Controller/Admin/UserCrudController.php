<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\User;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudControllerLib
{
    public function __construct(protected UserPasswordHasherInterface $userPasswordHasher)
    {
    }

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
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Répéter le mot de passe'],
                'mapped'         => false,
            ]
        );
        $textField->setRequired(Crud::PAGE_NEW === $pageName);
        $textField->onlyOnForms();

        yield $textField;
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
