<?php

namespace Labstag\Event\Abstract;

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
use Labstag\Entity\Saga;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Labstag\Entity\Story;
use Labstag\Enum\PageEnum;
use Labstag\Message\MovieMessage;
use Labstag\Message\SagaMessage;
use Labstag\Message\SeasonMessage;
use Labstag\Message\SerieMessage;
use Labstag\Message\StoryMessage;
use Labstag\Repository\ChapterRepository;
use Labstag\Repository\HttpErrorLogsRepository;
use Labstag\Repository\PageRepository;
use Labstag\Repository\SeasonRepository;
use Labstag\Service\BlockService;
use Labstag\Service\ParagraphService;
use Labstag\Service\WorkflowService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Registry;

abstract class EventEntityLib
{
    public function __construct(
        #[Autowire(service: 'workflow.registry')]
        private Registry $workflowRegistry,
        protected MessageBusInterface $messageBus,
        protected WorkflowService $workflowService,
        protected ChapterRepository $chapterRepository,
        protected SeasonRepository $seasonRepository,
        protected EntityManagerInterface $entityManager,
        protected ParagraphService $paragraphService,
        protected BlockService $blockService,
        protected PageRepository $pageRepository,
        protected HttpErrorLogsRepository $httpErrorLogsRepository,
    )
    {
    }

    protected function initEntityMeta(object $instance): void
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

    protected function updateEntityBanIp(object $instance, EntityManagerInterface $entityManager): void
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

    protected function updateEntityBlock(object $instance): void
    {
        if (!$instance instanceof Block) {
            return;
        }

        $this->blockService->update($instance);
    }

    protected function updateEntityChapter(object $instance): void
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

        $this->messageBus->dispatch(new StoryMessage($instance->getRefstory()->getId()));
    }

    protected function updateEntityMovie(object $instance): void
    {
        if (!$instance instanceof Movie) {
            return;
        }

        $this->messageBus->dispatch(new MovieMessage($instance->getId()));
    }

    protected function updateEntityPage(object $instance): void
    {
        if (!$instance instanceof Page) {
            return;
        }

        if (PageEnum::HOME->value != $instance->getType()) {
            return;
        }

        $oldHome = $this->pageRepository->getOneByType(PageEnum::HOME->value);
        if (PageEnum::HOME->value == $instance->getType()) {
            $instance->setSlug('');
        }

        if ($oldHome instanceof Page && $oldHome->getId() === $instance->getId()) {
            return;
        }

        if ($oldHome instanceof Page) {
            $oldHome->setType(PageEnum::PAGE->value);
            $this->pageRepository->save($oldHome);
        }

        $instance->setSlug('');
    }

    protected function updateEntityParagraph(object $instance): void
    {
        if (!$instance instanceof Paragraph) {
            return;
        }

        $this->paragraphService->update($instance);
    }

    protected function updateEntityRedirection(object $instance): void
    {
        if (!$instance instanceof Redirection) {
            return;
        }

        $instance->incrementLastCount();
    }

    protected function updateEntitySaga(object $instance): void
    {
        if (!$instance instanceof Saga) {
            return;
        }

        $this->messageBus->dispatch(new SagaMessage($instance->getId()));
    }

    protected function updateEntitySeason(object $instance): void
    {
        if (!$instance instanceof Season) {
            return;
        }

        $this->messageBus->dispatch(new SeasonMessage($instance->getId()));
    }

    protected function updateEntitySerie(object $instance): void
    {
        if (!$instance instanceof Serie) {
            return;
        }

        $this->messageBus->dispatch(new SerieMessage($instance->getId()));
    }

    protected function updateEntityStory(object $instance): void
    {
        if (!$instance instanceof Story) {
            return;
        }

        $this->messageBus->dispatch(new StoryMessage($instance->getId()));
    }
}
