<?php

namespace Labstag\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Labstag\Entity\Movie;
use Labstag\Lib\AbstractCrudControllerLib;
use Labstag\Repository\SagaRepository;
use Labstag\Service\MovieService;
use Override;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class MovieCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $actions->add(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE);

        $action = $this->setLinkImdbAction();
        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);
        $this->setEditDetail($actions);
        $this->configureActionsTrash($actions);
        $this->configureActionsUpdateImage($actions);

        return $actions;
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['title' => 'ASC']
        );

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield $this->addTabPrincipal();
        yield $this->addFieldID();
        yield $this->addFieldTitle();
        yield TextField::new('imdb', new TranslatableMessage('Imdb'));
        yield IntegerField::new('year', new TranslatableMessage('Year'));
        yield TextField::new('country', new TranslatableMessage('Country'));
        yield TextField::new('color', new TranslatableMessage('Color'));
        yield IntegerField::new('duration', new TranslatableMessage('Duration'));
        yield $this->addFieldSaga('movie');
        yield $this->addFieldTags('movie');
        yield NumberField::new('evaluation', new TranslatableMessage('Evaluation'));
        yield IntegerField::new('votes', new TranslatableMessage('Votes'));
        yield TextField::new('trailer', new TranslatableMessage('Trailer'))->hideOnIndex();
        yield TextField::new('description', new TranslatableMessage('Description'))->hideOnIndex();
        yield $this->addFieldCategories('movie');
        yield $this->addFieldImageUpload('img', $pageName);
        yield $this->addFieldBoolean('enable', new TranslatableMessage('Enable'));
        $date = $this->addTabDate();
        foreach ($date as $field) {
            yield $field;
        }
    }

    protected function addFieldSaga(): AssociationField
    {
        
        $associationField = AssociationField::new('saga', new TranslatableMessage('Saga'));
        $associationField->autocomplete();
        $associationField->setSortProperty('title');

        return $associationField;
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->addFilterEnable($filters);
        $filters->add('year');
        $filters->add('country');
        $filters->add('color');

        $this->addFilterTags($filters, 'movie');
        $this->addFilterCategories($filters, 'movie');
        $this->addFilterSaga($filters);

        return $filters;
    }

    protected function addFilterSaga(Filters $filters): void
    {
        $entityFilter = EntityFilter::new('saga', new TranslatableMessage('Sagas'));
        $filters->add($entityFilter);
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Movie::class;
    }

    #[Route('/admin/movieimageupdate', name: 'admin_movie_imageupdate')]
    public function image(MovieService $movieService): RedirectResponse
    {
        $serviceEntityRepositoryLib = $this->getRepository();
        if (!method_exists($serviceEntityRepositoryLib, 'findTrailerImageDescriptionIsNull')) {
            $this->addFlash('danger', new TranslatableMessage('Method not found'));

            return $this->redirectToRoute('admin_movie_index');
        }

        $movies  = $serviceEntityRepositoryLib->findTrailerImageDescriptionIsNull();

        $counter = 0;
        $update  = 0;
        foreach ($movies as $movie) {
            $status = $movieService->update($movie);
            $update = $status ? ++$update : $update;
            ++$counter;

            $serviceEntityRepositoryLib->persist($movie);
            $serviceEntityRepositoryLib->flush($counter);
        }

        $serviceEntityRepositoryLib->flush();

        $this->addFlash(
            'success',
            new TranslatableMessage(
                'Update %update% movie(s)',
                ['%update%' => $update]
            )
        );

        return $this->redirectToRoute('admin_movie_index');
    }

    #[Route('/admin/movie/{entity}/imdb', name: 'admin_movie_imdb')]
    public function imdb(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryLib = $this->getRepository();
        $movie                      = $serviceEntityRepositoryLib->find($entity);

        return $this->redirect('https://www.imdb.com/title/' . $movie->getImdb() . '/');
    }

    private function configureActionsUpdateImage(Actions $actions): void
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $action = $request->query->get('action', null);
        if ('trash' == $action) {
            return;
        }

        $action = Action::new('updateimage', new TranslatableMessage('Update Images'), 'fas fa-wrench');
        $action->linkToUrl(fn (): string => $this->generateUrl('admin_movie_imageupdate'));
        $action->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $action);
    }

    private function setLinkImdbAction(): Action
    {
        $action = Action::new('imdb', new TranslatableMessage('IMDB Page'));
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->linkToUrl(
            fn (Movie $movie): string => $this->generateUrl(
                'admin_movie_imdb',
                [
                    'entity' => $movie->getId(),
                ]
            )
        );
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        return $action;
    }
}
