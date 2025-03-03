<?php

namespace Labstag\Event\Subscriber;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Registry;

class EasyadminSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Registry $workflowRegistry,
        private WorkflowService $workflowService,
        private EntityManagerInterface $entityManager,
        private ParagraphService $paragraphService,
        private BlockService $blockService,
        private StoryService $storyService,
        private MovieService $movieService,
        private PageRepository $pageRepository,
        private HttpErrorLogsRepository $httpErrorLogsRepository,
    )
    {
    }

    public function beforePersisted(BeforeEntityPersistedEvent $beforeEntityPersistedEvent): void
    {
        $instance = $beforeEntityPersistedEvent->getEntityInstance();
        $this->initworkflow($instance);
        $this->initEntityMeta($instance);
    }

    private function initEntityMeta($instance): void
    {
        $tab = [
            Page::class,
            Chapter::class,
            Post::class,
        ];

        if (!in_array($instance::class, $tab)) {
            return;
        }

        $meta = $instance->getMeta();
        if (!$meta instanceof Meta) {
            $meta = new Meta();
            $instance->setMeta($meta);
        }
    }

    public function beforeUpdated(BeforeEntityUpdatedEvent $beforeEntityUpdatedEvent): void
    {
        $beforeEntityUpdatedEvent->getEntityInstance();
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

    public function afterPersisted(AfterEntityPersistedEvent $afterEntityPersistedEvent): void
    {
        $instance = $afterEntityPersistedEvent->getEntityInstance();
        $this->updateEntityParagraph($instance);
        $this->updateEntityBlock($instance);
        $this->updateEntityBanIp($instance, $this->entityManager);
        $this->updateEntityStory($instance);
        $this->updateEntityMovie($instance);
        $this->updateEntityChapter($instance);
        $this->updateEntityPage($instance);

        $this->entityManager->flush();
    }

    public function afterUpdated(AfterEntityUpdatedEvent $afterEntityUpdatedEvent): void
    {
        $instance = $afterEntityUpdatedEvent->getEntityInstance();
        $this->updateEntityParagraph($instance);
        $this->updateEntityBlock($instance);
        $this->updateEntityBanIp($instance, $this->entityManager);
        $this->updateEntityStory($instance);
        $this->updateEntityMovie($instance);
        $this->updateEntityChapter($instance);
        $this->updateEntityPage($instance);

        $this->entityManager->flush();
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

    private function updateEntityBanIp($instance, \Doctrine\ORM\EntityManagerInterface $entityManager): void
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

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['beforePersisted'],
            BeforeEntityUpdatedEvent::class   => ['beforeUpdated'],
            AfterEntityPersistedEvent::class  => ['afterPersisted'],
            AfterEntityUpdatedEvent::class    => ['afterUpdated'],
        ];
    }
}
