<?php

namespace Labstag\Event\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Labstag\Entity\Chapter;
use Labstag\Entity\Meta;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Repository\PageRepository;
use Labstag\Service\WorkflowService;

#[AsDoctrineListener(event: Events::prePersist)]
final class EntityListener
{
    public function __construct(
        protected PageRepository $pageRepository,
        protected WorkflowService $workflowService
    )
    {
    }

    public function prePersist(PrePersistEventArgs $prePersistEventArgs): void
    {
        $object = $prePersistEventArgs->getObject();
        $this->prePersistAddMeta($object);
        $this->prePersistChapter($object);
        $this->prePersistParagraph($object);
        $this->prePersistPage($object);
    }

    private function prePersistAddMeta($entity): void
    {
        $tab = [
            Page::class,
            Chapter::class,
            Post::class,
        ];

        if (!in_array($entity::class, $tab)) {
            return;
        }

        $meta = $entity->getMeta();
        if (!$meta instanceof Meta) {
            $meta = new Meta();
            $entity->setMeta($meta);
        }
    }

    private function prePersistChapter($entity): void
    {
        if (!$entity instanceof Chapter) {
            return;
        }

        if ($entity->getPosition() > 0) {
            return;
        }

        $history  = $entity->getRefhistory();
        $chapters = $history->getChapters();
        $entity->setPosition(count($chapters) + 1);
    }

    private function prePersistPage($entity): void
    {
        if (!$entity instanceof Page) {
            return;
        }

        if ('home' != $entity->getType()) {
            return;
        }

        $oldHome = $this->pageRepository->findOneBy(['type' => 'home']);
        if ($oldHome instanceof Page && $oldHome->getId() === $entity->getId()) {
            return;
        }

        if ($oldHome instanceof Page) {
            $oldHome->setType('page');
            $this->pageRepository->save($oldHome);
        }

        $entity->setSlug('');
    }

    private function prePersistParagraph($entity): void
    {
        if (!$entity instanceof Paragraph) {
            return;
        }

        dd($entity);
    }
}
