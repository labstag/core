<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use Labstag\Entity\Submission;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class SubmissionCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $actions->remove(Crud::PAGE_INDEX, Action::NEW);
        $actions->remove(Crud::PAGE_INDEX, Action::EDIT);

        return $actions;
    }

    public static function getEntityFqcn(): string
    {
        return Submission::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield $this->addFieldID();
        yield TextField::new('type', new TranslatableMessage('type'));
        yield $this->addCreatedAtField();
        yield $this->addUpdatedAtField();
    }
}
