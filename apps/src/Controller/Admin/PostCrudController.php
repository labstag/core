<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Meta;
use Labstag\Entity\Post;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

class PostCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield $this->addFieldID();
        yield $this->addFieldSlug();
        yield $this->addFieldBoolean();
        yield TextField::new('title');
        yield $this->addFieldRefUser();
    }

    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $post = new Post();
        $meta = new Meta();
        $post->addMeta($meta);

        return $post;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Post::class;
    }
}
