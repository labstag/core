<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use Labstag\Entity\Meta;
use Labstag\Entity\Post;
use Labstag\Field\WysiwygField;
use Labstag\Lib\AbstractCrudControllerLib;
use Labstag\Repository\PostRepository;
use Override;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class PostCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->setActionPublic($actions, 'admin_post_w3c', 'admin_post_public');
        $this->setEditDetail($actions);
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[Route('/admin/post/{entity}/w3c', name: 'admin_post_w3c')]
    public function w3c(string $entity): RedirectResponse
    {
        $repository = $this->getRepository();
        $post = $repository->find($entity);

        return $this->linkw3CValidator($post);
    }

    #[Route('/admin/post/{entity}/public', name: 'admin_post_public')]
    protected function linkPublic(string $entity): RedirectResponse
    {
        $repository = $this->getRepository();
        $post = $repository->find($entity);

        return $this->linkPublic($post);
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
        yield $this->addFieldImageUpload('img', $pageName);
        yield $this->addFieldTags('post');
        yield $this->addFieldCategories('post');
        yield WysiwygField::new('resume', new TranslatableMessage('resume'))->hideOnIndex();
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
        $date = $this->addTabDate();
        foreach ($date as $field) {
            yield $field;
        }
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
