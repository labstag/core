<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Platform;
use Labstag\Form\Admin\PlatformType;
use Labstag\Message\AddGameMessage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Translation\TranslatableMessage;

class PlatformCrudController extends CrudControllerAbstract
{
    public function addByApi(AdminContext $adminContext, MessageBusInterface $messageBus): Response
    {
        $request = $adminContext->getRequest();
        $id      = $request->query->get('id');
        $messageBus->dispatch(new AddGameMessage($id, 'platform'));

        return $this->redirectToRoute('admin_platform_index');
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->remove(Crud::PAGE_INDEX, Action::NEW);

        $action = Action::new('showModal', new TranslatableMessage('New platform'));
        $action->linkToCrudAction('showModal');
        $action->setHtmlAttributes(
            ['data-action' => 'show-modal']
        );
        $action->createAsGlobalAction();

        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);

        return $this->actionsFactory->show();
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['title' => 'ASC']
        );
        $crud->setEntityLabelInSingular(new TranslatableMessage('Platform'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Platforms'));

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());

        $textField = TextField::new('family', new TranslatableMessage('Family'));

        $integerField = IntegerField::new('generation', new TranslatableMessage('Generation'));

        $abbreviationField = TextField::new('abbreviation', new TranslatableMessage('Abbreviation'));

        $igdbField = TextField::new('igdb', new TranslatableMessage('Igdb'));
        $igdbField->hideOnIndex();

        $associationField = AssociationField::new('games', new TranslatableMessage('Games'));
        $associationField->formatValue(fn ($entity): int => count($entity));
        $associationField->hideOnForm();

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->slugField(),
                $this->crudFieldFactory->titleField(),
                $textField,
                $abbreviationField,
                $integerField,
                $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn()),
                $igdbField,
                $associationField,
            ]
        );
        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    public static function getEntityFqcn(): string
    {
        return Platform::class;
    }

    public function showModal(AdminContext $adminContext): Response
    {
        $request = $adminContext->getRequest();
        $form    = $this->createForm(PlatformType::class);
        $form->handleRequest($request);

        return $this->render(
            'admin/platform/new.html.twig',
            [
                'ea'   => $adminContext,
                'form' => $form->createView(),
            ]
        );
    }
}
