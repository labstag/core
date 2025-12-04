<?php

namespace Labstag\Controller\Admin;

use Labstag\Service\EmailService;
use Labstag\Service\Imdb\SerieService;
use Labstag\Service\FormService;
use Labstag\Service\FileService;
use Labstag\Service\SiteService;
use Labstag\Service\SlugService;
use Labstag\Service\Imdb\SeasonService;
use Labstag\Service\SecurityService;
use Labstag\Service\BlockService;
use Labstag\Service\Imdb\EpisodeService;
use Labstag\Service\Imdb\MovieService;
use Labstag\Service\Imdb\SagaService;
use Labstag\Service\ParagraphService;
use Labstag\Service\WorkflowService;
use Symfony\Component\HttpFoundation\RequestStack;
use Labstag\Service\UserService;
use Labstag\Controller\Admin\Factory\ActionsFactory;
use Labstag\Controller\Admin\Factory\CrudFieldFactory;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;
use Override;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Company;
use Labstag\Message\CompanyAllMessage;
use Labstag\Message\CompanyMessage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Translation\TranslatableMessage;

class CompanyCrudController extends CrudControllerAbstract
{

    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->setReadOnly(true);
        $this->setUpdateAction();
        $this->actionsFactory->setActionUpdateAll('updateAllCompany');

        return $this->actionsFactory->show();
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Company'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Companies'));
        $crud->setDefaultSort(
            ['title' => 'ASC']
        );

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());

        $textField = TextField::new('tmdb', new TranslatableMessage('Tmdb'));
        $textField->hideOnIndex();

        $urlField = TextField::new('url', new TranslatableMessage('Url'));
        $urlField->hideOnIndex();

        $associationField = AssociationField::new('series', new TranslatableMessage('Series'));
        $associationField->formatValue(fn ($entity): int => count($entity));
        $associationField->hideOnForm();

        $moviesField = AssociationField::new('movies', new TranslatableMessage('Movies'));
        $moviesField->formatValue(fn ($entity): int => count($entity));
        $moviesField->hideOnForm();

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->titleField(),
                $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn()),
                $textField,
                $urlField,
                $associationField,
                $moviesField,
            ]
        );

        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    public static function getEntityFqcn(): string
    {
        return Company::class;
    }

    public function jsonCompany(Request $request): JsonResponse
    {
        $entityId = $request->query->get('entityId');
        $repositoryAbstract                = $this->getRepository();
        $company                           = $repositoryAbstract->find($entityId);
        $details = $this->theMovieDbApi->getDetailsCompany($company);
        return new JsonResponse($details);
    }

    public function updateAllCompany(): RedirectResponse
    {
        $this->messageBus->dispatch(new CompanyAllMessage());
        return $this->redirectToRoute('admin_company_index');
    }

    public function updateCompany(
        Request $request,
    ): RedirectResponse
    {
        $entityId = $request->query->get('entityId');
        $repositoryAbstract                = $this->getRepository();
        $episode                           = $repositoryAbstract->find($entityId);
        $this->messageBus->dispatch(new CompanyMessage($episode->getId()));
        if ($request->headers->has('referer')) {
            $url = $request->headers->get('referer');
            if (is_string($url) && '' !== $url) {
                return $this->redirect($url);
            }
        }

        return $this->redirectToRoute('admin_company_index');
    }

    private function setUpdateAction(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('updateCompany', new TranslatableMessage('Update'));
        $action->linkToCrudAction('updateCompany');
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);

        $action = Action::new('jsonCompany', new TranslatableMessage('Json'), 'fas fa-server');
        $action->linkToCrudAction('jsonCompany');
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }
}
