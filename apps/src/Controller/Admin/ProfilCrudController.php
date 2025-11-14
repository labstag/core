<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Translation\TranslatableMessage;

class ProfilCrudController extends UserCrudController
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->setReadOnly(true);
        $this->actionsFactory->setShowDetail(false);
        $this->actionsFactory->add(Crud::PAGE_EDIT, Action::EDIT);

        return $this->actionsFactory->show();
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setPageTitle(Crud::PAGE_EDIT, 'Mon profil');
        $crud->setEntityPermission('ROLE_SUPER_ADMIN');
        $crud->setFormThemes(['admin/form.html.twig']);

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal(self::getEntityFqcn());
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

        $choiceField = ChoiceField::new('language', new TranslatableMessage('Language'));
        $choiceField->setChoices($this->userService->getLanguagesForChoices());

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                TextField::new('username', new TranslatableMessage('Username')),
                EmailField::new('email', new TranslatableMessage('Email')),
                $textField,
                $choiceField,
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
            $collectionField = CollectionField::new($key, $label);
            $collectionField->onlyOnDetail();
            $collectionField->formatValue(fn ($value): int => count($value));
            $fields[] = $collectionField;
        }

        $this->crudFieldFactory->addFieldsToTab('principal', $fields);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters;
    }

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    #[\Override]
    public function index(AdminContext $adminContext): Response
    {
        unset($adminContext);

        throw new AccessDeniedHttpException();
    }
}
