<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Submission;
use Labstag\FrontForm\FrontFormAbstract;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class SubmissionCrudController extends CrudControllerAbstract
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->setReadOnly(true);

        return $this->actionsFactory->show();
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [TextField::new('type', new TranslatableMessage('Type'))]
        );
        if (Action::DETAIL === $pageName) {
            $this->crudFieldFactory->addFieldsToTab('principal', $this->addFieldsSubmission($currentEntity));
        }

        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
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
