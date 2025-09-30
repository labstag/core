<?php

namespace Labstag\Controller\Admin\Traits;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

/**
 * Trait utilitaire pour rendre un CRUD en lecture seule (pas de NEW/EDIT/DELETE).
 */
trait ReadOnlyActionsTrait
{
    protected function applyReadOnly(Actions $actions): void
    {
        $actions->remove(Crud::PAGE_INDEX, Action::NEW);
        $actions->remove(Crud::PAGE_INDEX, Action::EDIT);
        $actions->remove(Crud::PAGE_INDEX, Action::DELETE);
        $actions->remove(Crud::PAGE_DETAIL, Action::DELETE);
        $actions->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE);
        $actions->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN);
    }
}
