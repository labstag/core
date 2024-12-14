<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use Labstag\Entity\User;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatableMessage;

class UserCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->setEditDetail($actions);
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('username', new TranslatableMessage('Username'));
        yield EmailField::new('email', new TranslatableMessage('Email'));
        yield $this->addFieldBoolean();
        $choiceField = ChoiceField::new('roles', new TranslatableMessage('Roles'));
        $choiceField->allowMultipleChoices();
        $choiceField->setChoices($this->userService->getRoles());
        yield $choiceField;
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
        $languageField = ChoiceField::new('language', new TranslatableMessage('Language'));
        $languageField->setChoices($this->userService->getLanguagesForChoices());
        yield $languageField;
        yield $this->addFieldImageUpload('avatar', $pageName);
        yield CollectionField::new('histories', new TranslatableMessage('Histories'))->onlyOnDetail();
        yield CollectionField::new('editos', new TranslatableMessage('Editos'))->onlyOnDetail()->formatValue(
            fn ($entity) => count($entity)
        );

        $tab = [
            'editos' => new TranslatableMessage('editos'),
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
    public function configureFilters(Filters $filters): Filters
    {
        $this->addFilterEnable($filters);

        return $filters;
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
        return $formBuilder->addEventListener(
            FormEvents::POST_SUBMIT,
            $this->hashPassword()
        );
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

            $hash = $this->userService->hashPassword($event->getData(), $password);
            $form->getData()->setPassword($hash);
        };
    }
}
