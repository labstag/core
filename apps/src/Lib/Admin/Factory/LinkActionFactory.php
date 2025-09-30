<?php

namespace Labstag\Lib\Admin\Factory;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Translation\TranslatableMessage;
use Labstag\Service\SlugService;

/**
 * Fabrique pour les actions (liens publics, validation W3C).
 */
final class LinkActionFactory
{
    public function __construct(private SlugService $slugService, private bool $w3cEnabled = false)
    {
    }

    public function publicAction(string $routeName): Action
    {
        return Action::new('linkPublic', new TranslatableMessage('View Page'))
            ->setHtmlAttributes(['target' => '_blank'])
            ->linkToUrl(fn ($entity): string => $this->buildPublicUrl($entity, $routeName))
            ->displayIf(static fn ($entity): bool => method_exists($entity, 'getDeletedAt') ? null === $entity->getDeletedAt() : true);
    }

    public function w3cAction(string $routeName): ?Action
    {
        if (!$this->w3cEnabled) {
            return null;
        }

        return Action::new('linkw3CValidator', new TranslatableMessage('W3C Validator'))
            ->setHtmlAttributes(['target' => '_blank'])
            ->linkToUrl(fn ($entity): string => $this->buildW3cUrl($entity, $routeName))
            ->displayIf(static fn ($entity): bool => method_exists($entity, 'getDeletedAt') ? null === $entity->getDeletedAt() : true);
    }

    public function applyTo(Actions $actions, string $publicRoute, ?string $w3cRoute = null): void
    {
        // Ajout sur toutes les pages principales
        $public = $this->publicAction($publicRoute);
        $actions->add(Crud::PAGE_DETAIL, $public);
        $actions->add(Crud::PAGE_EDIT, $public);
        $actions->add(Crud::PAGE_INDEX, $public);

        if ($w3cRoute && $this->w3cEnabled) {
            $w3c = $this->w3cAction($w3cRoute);
            if ($w3c) {
                $actions->add(Crud::PAGE_DETAIL, $w3c);
                $actions->add(Crud::PAGE_EDIT, $w3c);
                $actions->add(Crud::PAGE_INDEX, $w3c);
            }
        }
    }

    private function buildPublicUrl(object $entity, string $routeName): string
    {
        // Placeholder : sera remplacé par une génération via router dans le contrôleur.
        $short = (new \ReflectionClass($entity))->getShortName();

        return sprintf('/admin/%s/%s/public', strtolower($short), $entity->getId());
    }

    private function buildW3cUrl(object $entity, string $routeName): string
    {
        $short = (new \ReflectionClass($entity))->getShortName();

        return sprintf('/admin/%s/%s/w3c', strtolower($short), $entity->getId());
    }
}
