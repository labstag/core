<?php

namespace Labstag\Lib\Admin\Factory;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * Fabrique pour les actions (lien public + validation W3C optionnelle).
 * Ne génère pas directement l'URL front (c'est géré par les routes existantes de chaque CrudController).
 */
final class LinkActionFactory
{
    /**
     * @param bool $w3cEnabled Injecté via paramètre de config (app.w3c_enabled)
     */
    public function __construct(private bool $w3cEnabled = false)
    {
    }

    public function createPublicAction(string $routeName): Action
    {
        return Action::new('linkPublic', new TranslatableMessage('View Page'))
            ->setHtmlAttributes(['target' => '_blank'])
            ->linkToRoute($routeName, fn ($entity): array => ['entity' => $entity->getId()])
            ->displayIf(static fn ($entity): bool => !method_exists($entity, 'getDeletedAt') || null === $entity->getDeletedAt());
    }

    public function createW3cAction(string $routeName): ?Action
    {
        if (!$this->w3cEnabled) {
            return null;
        }

        return Action::new('linkw3CValidator', new TranslatableMessage('W3C Validator'))
            ->setHtmlAttributes(['target' => '_blank'])
            ->linkToRoute($routeName, fn ($entity): array => ['entity' => $entity->getId()])
            ->displayIf(static fn ($entity): bool => !method_exists($entity, 'getDeletedAt') || null === $entity->getDeletedAt());
    }

    public function apply(Actions $actions, string $publicRoute, ?string $w3cRoute = null): void
    {
        $public = $this->createPublicAction($publicRoute);
        $actions->add(Crud::PAGE_DETAIL, $public);
        $actions->add(Crud::PAGE_EDIT, $public);
        $actions->add(Crud::PAGE_INDEX, $public);

        if ($w3cRoute) {
            $w3c = $this->createW3cAction($w3cRoute);
            if ($w3c) {
                $actions->add(Crud::PAGE_DETAIL, $w3c);
                $actions->add(Crud::PAGE_EDIT, $w3c);
                $actions->add(Crud::PAGE_INDEX, $w3c);
            }
        }
    }
}
