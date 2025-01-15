<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use Labstag\Entity\Submission;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
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

    public static function getEntityFqcn(): string
    {
        return Submission::class;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        yield $this->addFieldID();
        yield TextField::new('type', new TranslatableMessage('type'));
        yield $this->addCreatedAtField();
        yield $this->addUpdatedAtField();
        if ($pageName === Action::DETAIL) {
            $fields = $this->addFieldsSubmission($currentEntity);
            foreach ($fields as $field) {
                yield $field;
            }
        }
    }

    private function addFieldsSubmission(Submission $submission): iterable
    {
        $data = $submission->getData();
        $form = $this->formService->get($submission->getType());
        if (!$form instanceof FrontFormLib) {
            return [];
        }

        $methods = get_class_methods($form);
        if (!in_array('getFields', $methods)) {
            return [];
        }

        return $form->getFields($data);
    }
}
