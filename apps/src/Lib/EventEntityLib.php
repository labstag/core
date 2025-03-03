<?php

namespace Labstag\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\BanIp;
use Labstag\Entity\Block;
use Labstag\Entity\Chapter;
use Labstag\Entity\Meta;
use Labstag\Entity\Movie;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Entity\Redirection;
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

abstract class EventEntityLib
{
    protected function __construct(
        #[Autowire(service: 'workflow.registry')]
        private Registry $workflowRegistry,
        protected WorkflowService $workflowService,
        protected EntityManagerInterface $entityManager,
        protected ParagraphService $paragraphService,
        protected BlockService $blockService,
        protected StoryService $storyService,
        protected MovieService $movieService,
        protected PageRepository $pageRepository,
        protected HttpErrorLogsRepository $httpErrorLogsRepository,
    )
    {
    }

    protected function initEntityMeta($instance): void
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

    protected function initworkflow(object $object): void
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

    protected function updateEntityPage($instance): void
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

    protected function updateEntityChapter($instance): void
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

    protected function updateEntityMovie($instance): void
    {
        if (!$instance instanceof Movie) {
            return;
        }

        $this->movieService->update($instance);
    }

    protected function updateEntityStory($instance): void
    {
        if (!$instance instanceof Story) {
            return;
        }

        $this->storyService->setPdf($instance);
        $this->storyService->generateFlashBag();
    }

    protected function updateEntityRedirection($instance, EntityManagerInterface $entityManager): void
    {
        if (!$instance instanceof Redirection) {
            return;
        }

        $httpsLogs = $this->httpErrorLogsRepository->findBy(
            [
                'url' => $instance->getSource(),
            ]
        );
        foreach ($httpsLogs as $httpLog) {
            $entityManager->remove($httpLog);
        }
    }

    protected function updateEntityBanIp($instance, EntityManagerInterface $entityManager): void
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

    protected function updateEntityParagraph($instance): void
    {
        if (!$instance instanceof Paragraph) {
            return;
        }

        $this->paragraphService->update($instance);
    }

    protected function updateEntityBlock($instance): void
    {
        if (!$instance instanceof Block) {
            return;
        }

        $this->blockService->update($instance);
    }
}
