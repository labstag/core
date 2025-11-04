<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Labstag\Entity\PostTag;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Translation\TranslatableMessage;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Labstag\Entity\Tag;

class PostTagCrudController extends TagCrudControllerAbstract
{
    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal(self::getEntityFqcn());
        $titleField = $this->crudFieldFactory->titleField();
        $titleField->setFormattedValue(
            function($entity) {
                return $entity->getTitle() ?? (new TranslatableMessage('Label not found'));
            }
        );
        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $titleField
            ]
        );
        $collectionField = CollectionField::new('posts', new TranslatableMessage('Posts'));
        $collectionField->formatValue(fn ($entity): int => count($entity));
        $collectionField->hideOnForm();

        $this->crudFieldFactory->addFieldsToTab('principal', [$collectionField]);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return PostTag::class;
    }
}
