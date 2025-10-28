<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use Labstag\Entity\Meta;
use Labstag\Entity\Post;
use Labstag\Field\WysiwygField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class PostCrudController extends CrudControllerAbstract
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
        $crud->setEntityLabelInSingular(new TranslatableMessage('Post'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Posts'));
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal();
        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->addFieldIDShortcode('post'),
                $this->crudFieldFactory->idField(),
                $this->crudFieldFactory->slugField(),
                $this->crudFieldFactory->booleanField('enable', (string) new TranslatableMessage('Enable')),
                $this->crudFieldFactory->titleField(),
                $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn()),
            ]
        );

        $this->crudFieldFactory->addFieldsToTab('principal', $this->crudFieldFactory->taxonomySet('post'));

        // Additional specific field (resume) not yet in factory bundle - placed at end of principal tab
        $wysiwygField = WysiwygField::new('resume', new TranslatableMessage('resume'));
        $wysiwygField->hideOnIndex();

        $this->crudFieldFactory->addFieldsToTab('principal', [$wysiwygField]);

        $this->crudFieldFactory->setTabParagraphs($pageName);
        $this->crudFieldFactory->setTabSEO();
        $this->crudFieldFactory->setTabUser($this->isSuperAdmin());

        $this->crudFieldFactory->setTabWorkflow();
        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields();
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
        $serviceEntityRepositoryAbstract = $this->getRepository();
        $post                            = $serviceEntityRepositoryAbstract->find($entity);

        return $this->publicLink($post);
    }

    #[Route('/admin/post/{entity}/w3c', name: 'admin_post_w3c')]
    public function w3c(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryAbstract = $this->getRepository();
        $post                            = $serviceEntityRepositoryAbstract->find($entity);

        return $this->linkw3CValidator($post);
    }
}
