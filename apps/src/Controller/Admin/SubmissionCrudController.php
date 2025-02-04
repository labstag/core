<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Submission;
use Labstag\Lib\AbstractCrudControllerLib;
use Labstag\Lib\FrontFormLib;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class SubmissionCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $actions->remove(Crud::PAGE_INDEX, Action::NEW);
        $actions->remove(Crud::PAGE_INDEX, Action::EDIT);
        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);

        return $actions;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        yield $this->addFieldID();
        yield TextField::new('type', new TranslatableMessage('type'));
        yield $this->addCreatedAtField();
        yield $this->addUpdatedAtField();
        if (Action::DETAIL === $pageName) {
            $fields = $this->addFieldsSubmission($currentEntity);
            foreach ($fields as $field) {
                yield $field;
            }
        }
    }

    public static function getEntityFqcn(): string
    {
        return Submission::class;
    }

    private function addFieldsSubmission(Submission $submission): iterable
    {
        $data = $submission->getData();
        $form = $this->formService->get($submission->getType());
        if (!$form instanceof FrontFormLib) {
            return [];
        }

        if (!method_exists($form, 'getFields')) {
            return [];
        }

        return $form->getFields($data);
    }
}
