<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Game;
use Labstag\Form\Admin\GameType;
use Labstag\Message\AddGameMessage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Translation\TranslatableMessage;

class GameCrudController extends CrudControllerAbstract
{
    public function addByApi(AdminContext $adminContext, MessageBusInterface $messageBus): Response
    {
        $request  = $adminContext->getRequest();
        $id       = $request->query->get('id');
        $platform = $request->query->get('platform');
        $messageBus->dispatch(new AddGameMessage($id, 'game', $platform));

        return $this->redirectToRoute('admin_game_index');
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->remove(Crud::PAGE_INDEX, Action::NEW);

        $action = Action::new('showModal', new TranslatableMessage('New game'));
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
        $crud->setEntityLabelInSingular(new TranslatableMessage('Game'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Games'));

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());

        $textField = TextField::new('igdb', new TranslatableMessage('Igdb'));
        $textField->hideOnIndex();

        $associationField = AssociationField::new('platforms', new TranslatableMessage('Platforms'));
        $associationField->formatValue(fn ($entity): int => count($entity));

        $categoryField = AssociationField::new('categories', new TranslatableMessage('Categories'));
        $categoryField->formatValue(fn ($entity): int => count($entity));

        $this->crudFieldFactory->setTabDate($pageName);
        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->slugField(),
                $this->crudFieldFactory->titleField(),
                $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn()),
                DateField::new('releaseDate', new TranslatableMessage('Release date')),
                $textField,
                $associationField,
                $categoryField,
            ]
        );

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    public static function getEntityFqcn(): string
    {
        return Game::class;
    }

    public function showModal(AdminContext $adminContext): Response
    {
        $request = $adminContext->getRequest();
        $form    = $this->createForm(GameType::class);
        $form->handleRequest($request);

        return $this->render(
            'admin/game/new.html.twig',
            [
                'ea'   => $adminContext,
                'form' => $form->createView(),
            ]
        );
    }
}
