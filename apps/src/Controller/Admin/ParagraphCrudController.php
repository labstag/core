<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Paragraph;
use Labstag\Field\ParagraphParentField;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

class ParagraphCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        if ($this->isIframeEdit()) {
            $crud->renderSidebarMinimized();
            $crud->overrideTemplates(
                ['layout' => 'admin/paragraph/layout.html.twig']
            );
        }

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        unset($pageName);
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        yield $this->addFieldID();
        yield ChoiceField::new('fond', 'Fond')->setChoices($this->paragraphService->getFonds());
        $allTypes = array_flip($this->paragraphService->getAll(null));
        yield TextField::new('type')->hideOnForm()->formatValue(
            static fn ($value) => $allTypes[$value] ?? null
        );
        yield ParagraphParentField::new('parent', 'Parent');
        yield DateTimeField::new('created')->hideOnForm();
        yield DateTimeField::new('updated')->hideOnForm();
        if (is_null($currentEntity)) {
            return;
        }

        $fields = $this->paragraphService->getFields($currentEntity);
        foreach ($fields as $field) {
            yield $field;
        }
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Paragraph::class;
    }

    private function isIframeEdit()
    {
        $query = $this->requestStack->getCurrentRequest()->query->all();

        return isset($query['iframe']) && isset($query['crudAction']) && 'edit' === $query['crudAction'];
    }
}