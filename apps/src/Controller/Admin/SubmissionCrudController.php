<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Controller\Admin\Abstract\AbstractCrudControllerLib;
use Labstag\Entity\Submission;
use Labstag\FrontForm\FrontFormAbstract;
use Symfony\Component\Translation\TranslatableMessage;

class SubmissionCrudController extends AbstractCrudControllerLib
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $actions->remove(Crud::PAGE_INDEX, Action::NEW);
        $actions->remove(Crud::PAGE_INDEX, Action::EDIT);
        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);

        return $actions;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        yield $this->crudFieldFactory->idField();
        yield TextField::new('type', new TranslatableMessage('Type'));
        if (Action::DETAIL === $pageName) {
            $fields = $this->addFieldsSubmission($currentEntity);
            foreach ($fields as $field) {
                yield $field;
            }
        }

        foreach ($this->crudFieldFactory->dateSet($pageName) as $field) {
            yield $field;
        }
    }

    public static function getEntityFqcn(): string
    {
        return Submission::class;
    }

    /**
     * @return iterable<FieldInterface>
     */
    private function addFieldsSubmission(
        Submission $submission,
    ): iterable
    {
        $data = $submission->getData();
        $form = $this->formService->get($submission->getType());
        if (!$form instanceof FrontFormAbstract) {
            return [];
        }

        return $form->getFields($data);
    }
}
