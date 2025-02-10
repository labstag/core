<?php

namespace Labstag\Event\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\BanIp;
use Labstag\Entity\Chapter;
use Labstag\Entity\Meta;
use Labstag\Entity\Movie;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Entity\Story;
use Labstag\Repository\HttpErrorLogsRepository;
use Labstag\Repository\PageRepository;
use Labstag\Service\MovieService;
use Labstag\Service\ParagraphService;
use Labstag\Service\StoryService;
use Labstag\Service\WorkflowService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Workflow\Registry;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::postPersist)]
final class EntityListener
{
    public function __construct(
        #[Autowire(service: 'workflow.registry')]
        private Registry $workflowRegistry,
        private StoryService $storyService,
        private MovieService $movieService,
        private PageRepository $pageRepository,
        private HttpErrorLogsRepository $httpErrorLogsRepository,
        private ParagraphService $paragraphService,
        private WorkflowService $workflowService,
    )
    {
    }

    public function postPersist(PostPersistEventArgs $postPersistEventArgs): void
    {
        $object        = $postPersistEventArgs->getObject();
        $entityManager = $postPersistEventArgs->getObjectManager();
        $this->postPersistParagraph($object, $entityManager);
        $this->postPersistBanIp($object, $entityManager);
    }

    public function prePersist(PrePersistEventArgs $prePersistEventArgs): void
    {
        $object        = $prePersistEventArgs->getObject();
        $entityManager = $prePersistEventArgs->getObjectManager();
        $this->prePersistBanIp($object, $entityManager);
        $this->prePersistAddMeta($object, $entityManager);
        $this->prePersistChapter($object, $entityManager);
        $this->prePersistParagraph($object, $entityManager);
        $this->prePersistPage($object, $entityManager);
        $this->prePersistMovie($object, $entityManager);
        $this->prePersistStory($object, $entityManager);
        $this->initWorkflow($object);
    }

    public function prePersistStory($object, ObjectManager $objectManager): void
    {
        unset($objectManager);
        if (!$object instanceof Story) {
            return;
        }

        $this->storyService->setPdf($object);

        $this->storyService->generateFlashBag();
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

    private function postPersistBanIp($object, ObjectManager $objectManager): void
    {
        if (!$object instanceof BanIp) {
            return;
        }

        $httpsLogs = $this->httpErrorLogsRepository->findBy(
            [
                'internetProtocol' => $object->getInternetProtocol(),
            ]
        );
        foreach ($httpsLogs as $httpLog) {
            $objectManager->remove($httpLog);
        }

        $objectManager->flush();
    }

    private function postPersistParagraph(object $paragraph, ObjectManager $objectManager): void
    {
        if (!$paragraph instanceof Paragraph) {
            return;
        }

        if ('' != $paragraph->getType()) {
            return;
        }

        $objectManager->remove($paragraph);
    }

    private function prePersistAddMeta(object $entity, ObjectManager $objectManager): void
    {
        unset($objectManager);
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

    private function prePersistBanIp($object, ObjectManager $objectManager): void
    {
        if (!$object instanceof BanIp) {
            return;
        }

        $httpsLogs = $this->httpErrorLogsRepository->findBy(
            [
                'internetProtocol' => $object->getInternetProtocol(),
            ]
        );
        foreach ($httpsLogs as $httpLog) {
            $objectManager->remove($httpLog);
        }

        $objectManager->flush();
    }

    private function prePersistChapter(object $entity, ObjectManager $objectManager): void
    {
        unset($objectManager);
        if (!$entity instanceof Chapter) {
            return;
        }

        if (0 < $entity->getPosition()) {
            return;
        }

        $story    = $entity->getRefstory();
        $chapters = $story->getChapters();
        $entity->setPosition(count($chapters) + 1);

        $this->storyService->setPdf($entity->getRefstory());

        $this->storyService->generateFlashBag();
    }

    private function prePersistMovie(object $entity, ObjectManager $objectManager): void
    {
        $this->prePersistMovieImage($entity, $objectManager);
        $this->prePersistMovieDescription($entity, $objectManager);
    }

    private function prePersistMovieDescription(object $entity, ObjectManager $objectManager): void
    {
        unset($objectManager);
        if (!$entity instanceof Movie) {
            return;
        }

        if (!in_array($entity->getDescription(), [null, '', '0'], true)) {
            return;
        }

        $this->movieService->updateDescription($entity);
    }

    private function prePersistMovieImage(object $entity, ObjectManager $objectManager): void
    {
        unset($objectManager);
        if (!$entity instanceof Movie) {
            return;
        }

        if (!in_array($entity->getImg(), [null, '', '0'], true)) {
            return;
        }

        $this->movieService->updateImage($entity);
    }

    private function prePersistPage(object $entity, ObjectManager $objectManager): void
    {
        unset($objectManager);
        if (!$entity instanceof Page) {
            return;
        }

        if ('home' != $entity->getType()) {
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

    private function prePersistParagraph(object $entity, ObjectManager $objectManager): void
    {
        unset($objectManager);
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
