<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Movie;
use Labstag\Entity\Recommendation;
use Labstag\Entity\Saga;
use Labstag\Entity\Serie;
use Override;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatableMessage;

class RecommendationCrudController extends CrudControllerAbstract
{
    public function addToBdd(Request $request): ?RedirectResponse
    {
        $repositoryAbstract = $this->getRepository();
        $entity             = $repositoryAbstract->find($request->query->get('entityId'));
        if (!$entity instanceof Recommendation) {
            return $this->redirectToRoute('admin_recommendation_index');
        }

        $tmdbId   = $entity->getTmdb();
        $refserie = $entity->getRefserie();
        if ($refserie instanceof Serie) {
            return $this->serieService->addToBddSerie($entity, $tmdbId);
        }

        $refmovie = $entity->getRefmovie();
        $refsaga  = $entity->getRefsaga();
        if ($refmovie instanceof Movie || $refsaga instanceof Saga) {
            return $this->movieService->addToBddMovie($entity, $tmdbId);
        }

        return $this->redirectToRoute('admin_recommendation_index');
    }

    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->setReadOnly(true);
        $this->setAddToBdd();
        $this->actionsFactory->setLinkTmdbAction();
        $this->actionsFactory->disableDelete();

        return $this->actionsFactory->show();
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Recommendation'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Recommendations'));
        $crud->setDefaultSort([
                'title' => 'ASC',
            ]);

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());

        $textField = TextField::new('tmdb', new TranslatableMessage('Tmdb'));
        $textField->hideOnIndex();

        $imgField = TextField::new('poster', new TranslatableMessage('Poster'));
        $imgField->addJsFiles(
            Asset::fromEasyAdminAssetPackage('field-image.js'),
            Asset::fromEasyAdminAssetPackage('field-file-upload.js')
        );
        $imgField->setTemplatePath('admin/field/fieldurlimg.html.twig');

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $textField,
                $imgField,
                TextField::new('title', new TranslatableMessage('Title')),
                TextField::new('overview', new TranslatableMessage('Overview')),
                DateField::new('releaseDate', new TranslatableMessage('Release date')),
            ]
        );

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    public static function getEntityFqcn(): string
    {
        return Recommendation::class;
    }

    public function setAddToBdd(): void
    {
        $action = Action::new('addToBdd', new TranslatableMessage('addToBdd'), 'fas fa-terminal');
        $action->renderAsLink();
        $action->linkToCrudAction('addToBdd');
        $action->setHtmlAttributes([
                'target' => '_blank',
            ]);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }

    public function tmdb(Request $request): RedirectResponse
    {
        $entityId                                 = $request->query->get('entityId');
        $repositoryAbstract                       = $this->getRepository();
        $recommendation                           = $repositoryAbstract->find($entityId);
        if ($recommendation->getRefserie() instanceof Serie) {
            return $this->redirect('https://www.themoviedb.org/tv/' . $recommendation->getTmdb());
        }

        return $this->redirect('https://www.themoviedb.org/movie/' . $recommendation->getTmdb());
    }
}
