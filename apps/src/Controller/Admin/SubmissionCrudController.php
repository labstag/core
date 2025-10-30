<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Submission;
use Labstag\FrontForm\FrontFormAbstract;
use Symfony\Component\Translation\TranslatableMessage;

class SubmissionCrudController extends CrudControllerAbstract
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
        $this->crudFieldFactory->setTabPrincipal(self::getEntityFqcn());
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [TextField::new('type', new TranslatableMessage('Type'))]
        );
        if (Action::DETAIL === $pageName) {
            $this->crudFieldFactory->addFieldsToTab('principal', [$this->addFieldsSubmission($currentEntity)]);
        }

        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields();
    }

    public static function getEntityFqcn(): string
    {
        return Submission::class;
    }

    /**
     * @return iterable<FieldInterface>
     */
    private function addFieldsSubmission(Submission $submission): iterable
    {
        $data = $submission->getData();
        $form = $this->formService->get($submission->getType());
        if (!$form instanceof FrontFormAbstract) {
            return [];
        }

        return $form->getFields($data);
    }
}
