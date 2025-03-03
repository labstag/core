<?php

namespace Labstag\Event\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Labstag\Entity\BanIp;
use Labstag\Entity\Block;
use Labstag\Entity\Chapter;
use Labstag\Entity\Meta;
use Labstag\Entity\Movie;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Entity\Story;
use Labstag\Repository\HttpErrorLogsRepository;
use Labstag\Repository\PageRepository;
use Labstag\Service\BlockService;
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
        private BlockService $blockService,
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

        $this->updateEntityParagraph($object);
        $this->updateEntityBlock($object);
        $this->updateEntityBanIp($object, $entityManager);
        $this->updateEntityStory($object);
        $this->updateEntityMovie($object);
        $this->updateEntityChapter($object);
        $this->updateEntityPage($object);

        $entityManager->flush();
    }

    public function updateEntityPage($instance): void
    {
        if (!$instance instanceof Page) {
            return;
        }

        if ('home' != $instance->getType()) {
            return;
        }

        $oldHome = $this->pageRepository->findOneBy(
            ['type' => 'home']
        );
        if ($oldHome instanceof Page && $oldHome->getId() === $instance->getId()) {
            return;
        }

        if ($oldHome instanceof Page) {
            $oldHome->setType('page');
            $this->pageRepository->save($oldHome);
        }

        $instance->setSlug('');
    }

    private function updateEntityChapter($instance): void
    {
        if (!$instance instanceof Chapter) {
            return;
        }

        if (0 < $instance->getPosition()) {
            return;
        }

        $story    = $instance->getRefstory();
        $chapters = $story->getChapters();
        $instance->setPosition(count($chapters) + 1);

        $this->storyService->setPdf($instance->getRefstory());
        $this->storyService->generateFlashBag();
    }

    private function updateEntityMovie($instance): void
    {
        if (!$instance instanceof Movie) {
            return;
        }

        $this->movieService->update($instance);
    }

    private function updateEntityStory($instance): void
    {
        if (!$instance instanceof Story) {
            return;
        }

        $this->storyService->setPdf($instance);
        $this->storyService->generateFlashBag();
    }

    private function updateEntityBanIp($instance, $entityManager): void
    {
        if (!$instance instanceof BanIp) {
            return;
        }

        $httpsLogs = $this->httpErrorLogsRepository->findBy(
            [
                'internetProtocol' => $instance->getInternetProtocol(),
            ]
        );
        foreach ($httpsLogs as $httpLog) {
            $entityManager->remove($httpLog);
        }
    }

    private function updateEntityParagraph($instance): void
    {
        if (!$instance instanceof Paragraph) {
            return;
        }

        $this->paragraphService->update($instance);
    }

    private function updateEntityBlock($instance): void
    {
        if (!$instance instanceof Block) {
            return;
        }

        $this->blockService->update($instance);
    }

    public function prePersist(PrePersistEventArgs $prePersistEventArgs): void
    {
        $object        = $prePersistEventArgs->getObject();
        $prePersistEventArgs->getObjectManager();
        $this->initworkflow($object);
        $this->initEntityMeta($object);
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

    private function initEntityMeta($object): void
    {
        $tab = [
            Page::class,
            Chapter::class,
            Post::class,
        ];

        if (!in_array($object::class, $tab)) {
            return;
        }

        $meta = $object->getMeta();
        if (!$meta instanceof Meta) {
            $meta = new Meta();
            $object->setMeta($meta);
        }
    }
}
