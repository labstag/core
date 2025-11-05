<?php

namespace Labstag\Controller\Admin;

use DeviceDetector\DeviceDetector;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Controller\Admin\Traits\ReadOnlyActionsTrait;
use Labstag\Entity\HttpErrorLogs;
use Labstag\Field\HttpLogs\IsBotField;
use Labstag\Field\HttpLogs\SameField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class HttpErrorLogsCrudController extends CrudControllerAbstract
{
    use ReadOnlyActionsTrait;

    #[Route('/admin/http-error-logs/{entity}/banip', name: 'admin_http_error_logs_banip')]
    public function banIp(string $entity, Request $request): RedirectResponse
    {
        $repositoryAbstract              = $this->getRepository();
        $httpErrorLogs                   = $repositoryAbstract->find($entity);
        $internetProtocol                = $httpErrorLogs->getInternetProtocol();

        $redirectToRoute = $this->redirectToRoute('admin_http_error_logs_index');
        if ($request->headers->has('referer')) {
            $url = $request->headers->get('referer');
            if (is_string($url) && '' !== $url) {
                $redirectToRoute = $this->redirect($url);
            }
        }

        if ($this->securityService->getCurrentClientIp() === $internetProtocol) {
            $this->addFlash('danger', new TranslatableMessage("You can't ban your own IP"));

            return $redirectToRoute;
        }

        $this->securityService->addBan($internetProtocol);
        $this->addFlash(
            'success',
            new TranslatableMessage(
                'Ip %ip% banned',
                ['%ip%' => $internetProtocol]
            )
        );

        return $redirectToRoute;
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->configureActionsTrash($actions);
        $this->addToBan($actions);
        $this->addToRedirection($actions);
        $this->applyReadOnly($actions);

        return $actions;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('HTTP Error Log'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('HTTP Error Logs'));
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal(self::getEntityFqcn());
        $maxLength = Crud::PAGE_DETAIL === $pageName ? 1024 : 32;
        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                TextField::new('url', new TranslatableMessage('Url'))->setMaxLength($maxLength),
                TextField::new('domain', new TranslatableMessage('Domain'))->hideOnIndex(),
                TextField::new('agent', new TranslatableMessage('Agent'))->setMaxLength($maxLength),
                TextField::new('internetProtocol', new TranslatableMessage('IP')),
                IsBotField::new('bot', new TranslatableMessage('Bot')),
            ]
        );
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        if (!is_null($currentEntity)) {
            $deviceDetector = new DeviceDetector($currentEntity->getAgent());
            $deviceDetector->parse();
            $data = [
                'deviceDetector' => $deviceDetector,
                'currentEntity'  => $currentEntity,
            ];
            $info = ArrayField::new('info', new TranslatableMessage('Information'));
            $info->hideOnIndex();
            $info->setValue($data);
            $info->setTemplatePath('admin/field/httperrorlogs/info.html.twig');
            $this->crudFieldFactory->addFieldsToTab('principal', [$info]);
        }

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                TextField::new('referer', new TranslatableMessage('Referer'))->setMaxLength($maxLength),
                IntegerField::new('httpCode', new TranslatableMessage('HTTP code')),
                TextField::new('requestMethod', new TranslatableMessage('Request method')),
                SameField::new('nbr', new TranslatableMessage('Number')),
            ]
        );

        if (!is_null($currentEntity)) {
            $data      = $currentEntity->getRequestData();
            $datafield = ArrayField::new('data', new TranslatableMessage('Request DATA'));
            $datafield->hideOnIndex();
            $datafield->setTemplatePath('admin/field/httperrorlogs/request_data.html.twig');
            $datafield->setValue($data);
            $this->crudFieldFactory->addFieldsToTab('principal', [$datafield]);
        }

        $this->crudFieldFactory->setTabUser($this->isSuperAdmin());

        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterRefUserFor($filters, self::getEntityFqcn());
        // Pas de champ enable pour les logs => pas de filtre enable
        $filters->add('internetProtocol');
        $filters->add('httpCode');
        $filters->add('requestMethod');

        return $filters;
    }

    public static function getEntityFqcn(): string
    {
        return HttpErrorLogs::class;
    }

    private function addToBan(Actions $actions): void
    {
        $action = $this->setLinkBanAction();
        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);
    }

    private function addtoRedirection(Actions $actions): void
    {
        $action = $this->setLinkNewRedirectionAction();
        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);
    }

    private function setLinkBanAction(): Action
    {
        $action = Action::new('banIp', new TranslatableMessage('Ban Ip'));
        $action->linkToUrl(
            fn ($entity): string => $this->generateUrl(
                'admin_http_error_logs_banip',
                [
                    'entity' => $entity->getId(),
                ]
            )
        );
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        return $action;
    }

    private function setLinkNewRedirectionAction(): Action
    {
        $action = Action::new('newRedirection', new TranslatableMessage('new Redirection'));
        $action->linkToUrl(
            fn ($entity): string => $this->generateUrl(
                'admin_redirection_new',
                [
                    'source' => $entity->getUrl(),
                ]
            )
        );
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        return $action;
    }
}
