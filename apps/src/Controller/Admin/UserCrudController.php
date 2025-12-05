<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\User;
use Override;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatableMessage;

class UserCrudController extends CrudControllerAbstract
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);

        return $this->actionsFactory->show();
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('User'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Users'));
        $crud->setDefaultSort(
            ['username' => 'ASC']
        );

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());
        $choiceField = ChoiceField::new('roles', new TranslatableMessage('Roles'));
        $choiceField->allowMultipleChoices();
        $choiceField->setChoices($this->userService->getRoles());

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
        $textField->setRequired(false);
        $textField->onlyOnForms();

        $associationField = AssociationField::new('groups', new TranslatableMessage('Groups'));
        $associationField->setFormTypeOption('by_reference', false);

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                TextField::new('username', new TranslatableMessage('Username')),
                EmailField::new('email', new TranslatableMessage('Email')),
                $this->crudFieldFactory->booleanField('enable', new TranslatableMessage('Enable')),
                $choiceField,
                $textField,
                $associationField,
            ]
        );
        if (Crud::PAGE_NEW === $pageName) {
            $generatePasswordField = $this->crudFieldFactory->booleanField(
                'generatepassword',
                new TranslatableMessage('generate Password')
            );
            $generatePasswordField->setFormTypeOptions(
                ['mapped' => false]
            );
            $this->crudFieldFactory->addFieldsToTab('principal', [$generatePasswordField]);
        }

        $languageField = ChoiceField::new('language', new TranslatableMessage('Language'));
        $langue        = $this->userService->getLanguagesForChoices();
        $languageField->setChoices($langue);
        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $languageField,
                $this->crudFieldFactory->imageField('avatar', $pageName, self::getEntityFqcn()),
            ]
        );

        $tab = [
            'stories' => new TranslatableMessage('Stories'),
            'editos'  => new TranslatableMessage('Editos'),
            'memos'   => new TranslatableMessage('memos'),
            'pages'   => new TranslatableMessage('pages'),
            'posts'   => new TranslatableMessage('posts'),
        ];
        $fields = [];
        foreach ($tab as $key => $label) {
            $collectionField = AssociationField::new($key, $label);
            $collectionField->onlyOnDetail();
            $collectionField->formatValue(fn ($value): int => is_null($value) ? 0 : count($value));
            $fields[] = $collectionField;
        }

        $this->crudFieldFactory->addFieldsToTab('principal', $fields);
        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterEnable($filters);

        return $filters;
    }

    /**
     * @return FormBuilderInterface<mixed>
     */
    #[Override]
    public function createEditFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $keyValueStore,
        AdminContext $adminContext,
    ): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $keyValueStore, $adminContext);

        return $this->addPasswordEventListener($formBuilder);
    }

    #[Override]
    public function createEntity(string $entityFqcn): User
    {
        $user = parent::createEntity($entityFqcn);
        $this->workflowService->init($user);
        $langue = $this->userService->getLanguagesForChoices();
        $key    = array_key_first($langue);
        $user->setLanguage($langue[$key]);

        return $user;
    }

    /**
     * @return FormBuilderInterface<mixed>
     */
    #[Override]
    public function createNewFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $keyValueStore,
        AdminContext $adminContext,
    ): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $keyValueStore, $adminContext);

        return $this->addPasswordEventListener($formBuilder);
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    /**
     * @param FormBuilderInterface<mixed> $formBuilder
     *
     * @return FormBuilderInterface<mixed>
     */
    private function addPasswordEventListener(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        return $formBuilder->addEventListener(FormEvents::POST_SUBMIT, $this->hashPassword());
    }

    private function hashPassword(): callable
    {
        return function ($event): void
        {
            $form = $event->getForm();
            if (!$form->isValid()) {
                return;
            }

            if (!$form->has('generatepassword')) {
                return;
            }

            $generatepassword = $form->get('generatepassword')->getData();
            if ($generatepassword) {
                $password = bin2hex(random_bytes(10));
                $hash     = $this->userService->hashPassword($event->getData(), $password);
                $form->getData()->setPassword($hash);

                return;
            }

            $password = $form->get('password')->getData();
            if (is_null($password)) {
                return;
            }

            $hash = $this->userService->hashPassword($event->getData(), $password);
            $form->getData()->setPassword($hash);
        };
    }
}
