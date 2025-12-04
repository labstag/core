<?php

namespace Labstag\Controller\Admin\Traits;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Labstag\Entity\Paragraph;
use Labstag\Repository\ParagraphRepository;
use Labstag\Service\ParagraphService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait isolating paragraph management in admin.
 * It assumes that the consuming class has methods:
 *   - getRepository(): RepositoryAbstract
 *   - render(string $view, array $params = []): Response
 *   - redirect(string $url): RedirectResponse.
 */
trait ParagraphAdminTrait
{
    // Public paragraph management endpoints (add/delete/list/update)
    public function addParagraph(
        AdminContext $adminContext,
        AdminUrlGenerator $urlGenerator,
        ParagraphService $paragraphService,
    ): RedirectResponse
    {
        $request  = $adminContext->getRequest();
        $entityId = $request->query->get('entityId');

        $urlGenerator->setAction('listParagraph');
        $urlGenerator->setEntityId($entityId);

        $type = $request->request->get('paragraph');
        if (null !== $type) {
            $entity     = $this->getRepository()->find($entityId);
            if ($entity) {
                $paragraphService->addParagraph($entity, $type);
                $this->getRepository()->save($entity);
            }
        }

        return $this->redirect($urlGenerator->generateUrl());
    }

    public function deleteParagraph(
        AdminContext $adminContext,
        AdminUrlGenerator $urlGenerator,
    ): RedirectResponse
    {
        $request  = $adminContext->getRequest();
        $entityId = $request->query->get('entityId');
        $urlGenerator->setAction('listParagraph');

        $paragraphId = $request->request->get('paragraph');
        if (null !== $paragraphId) {
            $paragraph = $this->getRepository(Paragraph::class)->find($paragraphId);
            if (null !== $paragraph) {
                $this->getRepository(Paragraph::class)->remove($paragraph);
                $this->getRepository(Paragraph::class)->flush();
            }
        }

        $urlGenerator->setEntityId($entityId);

        return $this->redirect($urlGenerator->generateUrl());
    }

    public function listParagraph(AdminContext $adminContext): Response
    {
        $entityId   = $adminContext->getRequest()->query->get('entityId');
        $entity     = $this->getRepository()->find($entityId);
        $paragraphs = method_exists($entity, 'getParagraphs') ? $entity->getParagraphs() : [];

        return $this->render(
            'admin/pararaphs.html.twig',
            ['paragraphs' => $paragraphs]
        );
    }

    public function updateParagraph(
        AdminContext $adminContext,
        AdminUrlGenerator $urlGenerator,
    ): RedirectResponse
    {
        $request    = $adminContext->getRequest();
        $entityId   = $request->query->get('entityId');
        $urlGenerator->setAction('listParagraph');
        $paragraphs = $request->request->get('paragraphs');
        if (null !== $paragraphs) {
            $ids = explode(',', $paragraphs);
            foreach ($ids as $position => $id) {
                $paragraph = $this->getRepository(Paragraph::class)->find($id);
                if ($paragraph && method_exists($paragraph, 'setPosition')) {
                    $paragraph->setPosition($position + 1);
                    $this->getRepository(Paragraph::class)->save($paragraph);
                }
            }
        }

        $urlGenerator->setEntityId($entityId);

        return $this->redirect($urlGenerator->generateUrl());
    }
}
