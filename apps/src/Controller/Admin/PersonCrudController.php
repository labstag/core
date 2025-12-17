<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Person;
use Labstag\Field\WysiwygField;
use Labstag\Message\PersonAllMessage;
use Labstag\Message\PersonMessage;
use Override;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatableMessage;

class PersonCrudController extends CrudControllerAbstract
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->remove(Crud::PAGE_INDEX, Action::NEW);
        $this->actionsFactory->setLinkTmdbAction();
        $this->setUpdateAction();
        $this->actionsFactory->setActionUpdateAll('updateAllPerson');

        return $this->actionsFactory->show();
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Person'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Persons'));
        $crud->setDefaultSort(
            ['title' => 'ASC']
        );

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());
        $choiceField = ChoiceField::new('gender', new TranslatableMessage('Gender'));
        $choices     = [
            0 => new TranslatableMessage('Not set / not specified'),
            1 => new TranslatableMessage('Female'),
            2 => new TranslatableMessage('Male'),
            3 => new TranslatableMessage('Non-binary'),
        ];
        $data = [];
        foreach ($choices as $value => $label) {
            $data[$label->getMessage()] = $value;
        }

        $choiceField->setChoices($data);
        $choiceField->hideOnIndex();

        $wysiwgTranslation = new TranslatableMessage('Biography');
        $wysiwygField      = WysiwygField::new('biography', $wysiwgTranslation->getMessage());
        $wysiwygField->hideOnIndex();

        $textField = TextField::new('placeOfBirth', new TranslatableMessage('Place of birth'));
        $textField->hideOnIndex();

        $profileTranslation = new TranslatableMessage('Profile');
        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->slugField(),
                $this->crudFieldFactory->titleField(),
                $choiceField,
                $this->crudFieldFactory->imageField(
                    'profile',
                    $pageName,
                    self::getEntityFqcn(),
                    $profileTranslation->getMessage()
                ),
                DateField::new('birthday', new TranslatableMessage('Birthday')),
                DateField::new('deathday', new TranslatableMessage('Deathday')),
                $textField,
                $wysiwygField,
            ]
        );

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    public static function getEntityFqcn(): string
    {
        return Person::class;
    }

    public function jsonPerson(Request $request): JsonResponse
    {
        $entityId                        = $request->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $person                          = $repositoryAbstract->find($entityId);
        $details                         = $this->theMovieDbApi->getDetailPerson($person);

        return new JsonResponse($details);
    }

    public function tmdb(Request $request): RedirectResponse
    {
        $entityId                        = $request->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $movie                           = $repositoryAbstract->find($entityId);

        return $this->redirect('https://www.themoviedb.org/person/' . $movie->getTmdb());
    }

    public function updateAllPerson(): RedirectResponse
    {
        $this->messageBus->dispatch(new PersonAllMessage());

        return $this->redirectToRoute('admin_person_index');
    }

    public function updatePerson(Request $request): RedirectResponse
    {
        $entityId                        = $request->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $person                          = $repositoryAbstract->find($entityId);
        $this->messageBus->dispatch(new PersonMessage($person->getId()));
        if ($request->headers->has('referer')) {
            $url = $request->headers->get('referer');
            if (is_string($url) && '' !== $url) {
                return $this->redirect($url);
            }
        }

        return $this->redirectToRoute('admin_person_index');
    }

    private function setUpdateAction(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('updatePerson', new TranslatableMessage('Update'), 'fas fa-sync-alt');
        $action->linkToCrudAction('updatePerson');
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);

        $action = Action::new('jsonPerson', new TranslatableMessage('Json'), 'fas fa-server');
        $action->linkToCrudAction('jsonPerson');
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }
}
