<?php

namespace Labstag\Controller\Admin\Abstract;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Labstag\Entity\Tag;
use Symfony\Component\Translation\TranslatableMessage;

abstract class TagCrudControllerLib extends AbstractTypedCrudControllerLib
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        // Tag is not uploadable: no image and no enable field in the entity
        return $this->crudFieldFactory->baseIdentitySet(
            'tag',
            $pageName,
            self::getEntityFqcn(),
            withSlug: true,
            withImage: false,
            withEnable: false
        );
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Tag'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Tags'));

        return $crud;
    }

    public static function getEntityFqcn(): string
    {
        return Tag::class;
    }

    #[\Override]
    protected function getChildType(): string
    {
        return 'tag';
    }
}
