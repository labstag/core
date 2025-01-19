<?php

namespace Labstag\Event\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker\Factory;
use Labstag\Entity\Chapter;
use Labstag\Entity\Meta;
use Labstag\Entity\Movie;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Repository\PageRepository;
use Labstag\Service\ImdbService;
use Labstag\Service\ParagraphService;
use Labstag\Service\WorkflowService;
use Smknstd\FakerPicsumImages\FakerPicsumImagesProvider;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Workflow\Registry;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::postPersist)]
final class EntityListener
{
    public function __construct(
        #[Autowire(service: 'workflow.registry')]
        private Registry $workflowRegistry,
        private KernelInterface $kernel,
        private ImdbService $imdbService,
        private PageRepository $pageRepository,
        private ParagraphService $paragraphService,
        private WorkflowService $workflowService,
    )
    {
    }

    public function postPersist(PostPersistEventArgs $postPersistEventArgs): void
    {
        $object = $postPersistEventArgs->getObject();
        $entityManager = $postPersistEventArgs->getObjectManager();
        $this->postPersistParagraph($object, $entityManager);
    }

    public function prePersist(PrePersistEventArgs $prePersistEventArgs): void
    {
        $object = $prePersistEventArgs->getObject();
        $this->prePersistAddMeta($object);
        $this->prePersistChapter($object);
        $this->prePersistParagraph($object);
        $this->prePersistPage($object);
        $this->prePersistMovie($object);
        $this->initWorkflow($object);
    }

    private function initworkflow(object $object): void
    {
        $this->workflowService->init($object);
        if (!$this->workflowRegistry->has($object)) {
            return;
        }

        $workflow = $this->workflowRegistry->get($object);
        if (!$workflow->can($object, 'submit')) {
            return;
        }

        $workflow->apply($object, 'submit');
    }

    private function postPersistParagraph(object $paragraph, ObjectManager $objectManager): void
    {
        if (!$paragraph instanceof Paragraph) {
            return;
        }

        if ($paragraph->getType() != '') {
            return;
        }

        $objectManager->remove($paragraph);
    }

    private function prePersistAddMeta(object $entity): void
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

    private function prePersistChapter(object $entity): void
    {
        if (!$entity instanceof Chapter) {
            return;
        }

        if ($entity->getPosition() > 0) {
            return;
        }

        $story = $entity->getRefstory();
        $chapters = $story->getChapters();
        $entity->setPosition(count($chapters) + 1);
    }

    private function prePersistMovie(object $entity): void
    {
        if (!$entity instanceof Movie) {
            return;
        }

        if (!is_null($entity->getImg()) || $entity->getImg() != '') {
            return;
        }

        $this->imdbService->update($entity);
    }

    private function prePersistPage(object $entity): void
    {
        if (!$entity instanceof Page) {
            return;
        }

        if ($entity->getType() != 'home') {
            return;
        }

        $oldHome = $this->pageRepository->findOneBy(
            ['type' => 'home']
        );
        if ($oldHome instanceof Page && $oldHome->getId() === $entity->getId()) {
            return;
        }

        if ($oldHome instanceof Page) {
            $oldHome->setType('page');
            $this->pageRepository->save($oldHome);
        }

        $entity->setSlug('');
    }

    private function prePersistParagraph(object $entity): void
    {
        if (!$entity instanceof Paragraph) {
            return;
        }

        $entity->setEnable(true);

        $data = $this->paragraphService->getEntityParent($entity);
        if (is_null($data) || is_null($data->value)) {
            return;
        }

        $paragraphs = $data->value->getParagraphs();
        $entity->setPosition(count($paragraphs));
    }
}
