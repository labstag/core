<?php

namespace Labstag\Lib\Admin\Factory;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * Factory for actions (public link + optional W3C validation).
 * Does not directly generate front URLs (handled by existing routes in each CrudController).
 */
final class LinkActionFactory
{

    public function createPublicAction(string $routeName): Action
    {
        return Action::new('linkPublic', new TranslatableMessage('View Page'))
            ->setHtmlAttributes(['target' => '_blank'])
            ->linkToRoute($routeName, fn ($entity): array => ['entity' => $entity->getId()])
            ->displayIf(static fn ($entity): bool => !method_exists($entity, 'getDeletedAt') || null === $entity->getDeletedAt());
    }

    public function createW3cAction(string $routeName): ?Action
    {

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
