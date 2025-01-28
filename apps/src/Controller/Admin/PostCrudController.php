<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use Labstag\Entity\Meta;
use Labstag\Entity\Post;
use Labstag\Form\Paragraphs\PostType;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class PostCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->setActionPublic($actions);
        $this->setEditDetail($actions);
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield $this->addTabPrincipal();
        yield $this->addFieldID();
        yield $this->addFieldIDShortcode('post');
        yield $this->addFieldSlug();
        yield $this->addFieldBoolean('enable', new TranslatableMessage('Enable'));
        yield $this->addFieldTitle();
        yield $this->addCreatedAtField();
        yield $this->addUpdatedAtField();
        yield $this->addFieldImageUpload('img', $pageName);
        yield $this->addFieldTags('post');
        yield $this->addFieldCategories('post');
        $fields = array_merge(
            $this->addFieldParagraphs($pageName),
            $this->addFieldMetas(),
            $this->addFieldRefUser()
        );
        foreach ($fields as $field) {
            yield $field;
        }

        yield $this->addFieldWorkflow();
        yield $this->addFieldState();
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->addFilterRefUser($filters);
        $this->addFilterEnable($filters);
        $this->addFilterTags($filters, 'post');
        $this->addFilterCategories($filters, 'post');

        return $filters;
    }

    #[Override]
    public function createEntity(string $entityFqcn): Post
    {
        $post = new $entityFqcn();
        $this->workflowService->init($post);
        $post->setRefuser($this->getUser());
        $meta = new Meta();
        $post->setMeta($meta);

        return $post;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Post::class;
    }
}
