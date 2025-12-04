<?php

namespace Labstag\Controller\Admin;

use Override;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Platform;
use Labstag\Form\Admin\PlatformType;
use Labstag\Message\AddGameMessage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;

class PlatformCrudController extends CrudControllerAbstract
{

    public function addByApi(Request $request): JsonResponse
    {
        $id      = $request->query->get('id');
        $this->messageBus->dispatch(new AddGameMessage($id, 'platform'));
        return new JsonResponse(
            [
                'status'  => 'success',
                'id'      => $id,
                'message' => $this->translator->trans(new TranslatableMessage('Platform is being added')),
            ]
        );
    }

    public function apiPlatform(Request $request): Response
    {
        $page               = $request->query->get('page', 1);
        $limit              = $request->query->get('limit', 20);
        $offset             = ($page - 1) * $limit;
        $all                = $request->request->all();
        $data               = [
            'title'  => $all['platform']['title'] ?? '',
            'family' => $all['platform']['family'] ?? '',
        ];
        $platforms = $this->platformService->getPlatformApi($data, $limit, $offset);
        return $this->render(
            'admin/api/game/platform.html.twig',
            [
                'page'       => $page,
                'controller' => self::class,
                'platforms'  => $platforms,
            ]
        );
    }

    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->remove(Crud::PAGE_INDEX, Action::NEW);
        $this->addActionNewPlatform();

        return $this->actionsFactory->show();
    }

    #[Override]
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

    #[Override]
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

    public function showModalPlatform(Request $request): Response
    {
        $form    = $this->createForm(PlatformType::class);
        $form->handleRequest($request);
        return $this->render(
            'admin/platform/new.html.twig',
            [
                'controller' => self::class,
                'form'       => $form->createView(),
            ]
        );
    }

    private function addActionNewPlatform(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('showModalPlatform', new TranslatableMessage('New platform'));
        $action->linkToCrudAction('showModalPlatform');
        $action->setHtmlAttributes(
            ['data-action' => 'show-modal']
        );
        $action->createAsGlobalAction();

        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }
}
