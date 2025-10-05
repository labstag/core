<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use Labstag\Controller\Admin\Abstract\AbstractCrudControllerLib;
use Labstag\Entity\Meta;
use Labstag\Entity\Post;
use Labstag\Field\WysiwygField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class PostCrudController extends AbstractCrudControllerLib
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        // Actions de base (trash + navigation + détail)
        $this->configureActionsTrash($actions);
        $this->setEditDetail($actions);

        // Actions publiques et W3C via la factory (héritées abstrait)
        $this->setActionPublic($actions, 'admin_post_w3c', 'admin_post_public');

        return $actions;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        // Principal tab + full content set (identity + taxonomy + paragraphs + meta + ref user)
        yield $this->addTabPrincipal();
        $isSuperAdmin = $this->isSuperAdmin();

        // Base identity fields (id, title, slug, enable, image)
        foreach ($this->crudFieldFactory->baseIdentitySet('post', $pageName, self::getEntityFqcn()) as $field) {
            yield $field;
        }

        // Taxonomy fields (tags, categories)
        foreach ($this->crudFieldFactory->taxonomySet('post') as $field) {
            yield $field;
        }

        // Additional specific field (resume) not yet in factory bundle - placed at end of principal tab
        yield WysiwygField::new('resume', new TranslatableMessage('resume'))->hideOnIndex();

        // Paragraphs fields
        foreach ($this->crudFieldFactory->paragraphFields($pageName) as $field) {
            yield $field;
        }

        // Meta fields (creates SEO tab)
        foreach ($this->crudFieldFactory->metaFields() as $field) {
            yield $field;
        }

        // Ref user fields (creates User tab if super admin)
        foreach ($this->crudFieldFactory->refUserFields($isSuperAdmin) as $field) {
            yield $field;
        }

        // Workflow + states
        yield $this->crudFieldFactory->workflowField();
        yield $this->crudFieldFactory->stateField();
        // Dates
        foreach ($this->crudFieldFactory->dateSet() as $field) {
            yield $field;
        }
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterRefUser($filters);
        $this->crudFieldFactory->addFilterEnable($filters);
        $this->crudFieldFactory->addFilterTags($filters, 'post');
        $this->crudFieldFactory->addFilterCategories($filters, 'post');

        return $filters;
    }

    #[\Override]
    public function createEntity(string $entityFqcn): Post
    {
        $post = new $entityFqcn();
        $this->workflowService->init($post);
        $post->setRefuser($this->getUser());
        $meta = new Meta();
        $post->setMeta($meta);

        return $post;
    }

    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    #[Route('/admin/post/{entity}/public', name: 'admin_post_public')]
    public function linkPublic(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryLib = $this->getRepository();
        $post                       = $serviceEntityRepositoryLib->find($entity);

        return $this->publicLink($post);
    }

    #[Route('/admin/post/{entity}/w3c', name: 'admin_post_w3c')]
    public function w3c(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryLib = $this->getRepository();
        $post                       = $serviceEntityRepositoryLib->find($entity);

        return $this->linkw3CValidator($post);
    }
}
