<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Movie;
use Labstag\Lib\AbstractCrudControllerLib;
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
        yield $this->addFieldID();
        yield $this->addFieldTitle();
        yield TextField::new('imdb');
        yield IntegerField::new('year');
        yield TextField::new('country');
        yield TextField::new('color');
        yield IntegerField::new('duration');
        yield NumberField::new('evaluation');
        yield IntegerField::new('votes');
        yield TextField::new('trailer')->hideOnIndex();
        yield $this->addFieldCategories('movie');
        yield $this->addFieldImageUpload('img', $pageName);
        yield $this->addFieldBoolean('enable', new TranslatableMessage('Enable'));
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->addFilterEnable($filters);
        $filters->add('year');
        $filters->add('country');
        $filters->add('color');

        $this->addFilterCategories($filters, 'movie');

        return $filters;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Movie::class;
    }

    public function imdb(AdminContext $adminContext): RedirectResponse
    {
        $entity = $adminContext->getEntity()->getInstance();

        return $this->redirect('https://www.imdb.com/title/tt' . $entity->getImdb() . '/');
    }

    #[Route('/admin/movie/updateimage', name: 'admin_movie_updateimage')]
    public function updateimage(MovieService $movieService): RedirectResponse
    {
        $serviceEntityRepositoryLib = $this->getRepository();

        $movies = $this->getRepository()->findBy(
            ['img' => null]
        );

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

    private function configureActionsUpdateImage(Actions $actions): void
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $action = $request->query->get('action', null);
        if ('trash' == $action) {
            return;
        }

        $action = Action::new('updateimage', new TranslatableMessage('Update Images'), 'fas fa-wrench');
        $action->linkToRoute('admin_movie_updateimage');
        $action->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $action);
    }

    private function setLinkImdbAction(): Action
    {
        $action = Action::new('imdb', new TranslatableMessage('IMDB Page'));
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->linkToCrudAction('imdb');
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        return $action;
    }
}
