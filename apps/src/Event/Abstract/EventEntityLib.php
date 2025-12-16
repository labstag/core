<?php

namespace Labstag\Event\Abstract;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\BanIp;
use Labstag\Entity\Block;
use Labstag\Entity\Chapter;
use Labstag\Entity\HttpErrorLogs;
use Labstag\Entity\Meta;
use Labstag\Entity\Movie;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Redirection;
use Labstag\Entity\Saga;
use Labstag\Entity\Serie;
use Labstag\Entity\Story;
use Labstag\Enum\PageEnum;
use Labstag\Message\MovieMessage;
use Labstag\Message\SagaMessage;
use Labstag\Message\SerieMessage;
use Labstag\Message\StoryMessage;
use Labstag\Service\BlockService;
use Labstag\Service\Imdb\MovieService;
use Labstag\Service\ParagraphService;
use Labstag\Service\WorkflowService;
use ReflectionClass;
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
        protected EntityManagerInterface $entityManager,
        protected ParagraphService $paragraphService,
        protected MovieService $movieService,
        protected BlockService $blockService,
    )
    {
    }

    protected function addParagraph(object $instance, string $type, ?int $position = null): void
    {
        $classType  = $this->paragraphService->getByCode($type);
        if (is_null($classType)) {
            return;
        }

        $paragraphs = $instance->getParagraphs();
        foreach ($paragraphs as $paragraph) {
            if ($classType->getClass() == $paragraph::class) {
                return;
            }
        }

        $this->paragraphService->addParagraph($instance, $type, $position);
    }

    protected function initEntityMeta(object $instance): void
    {
        $reflectionClass = new ReflectionClass($instance);
        if (!$reflectionClass->hasMethod('getMeta')) {
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

    protected function postPersistMethods(object $object, EntityManagerInterface $entityManager)
    {
        $this->updateEntityStory($object);
        $this->updateEntityChapter($object);
        $this->updateEntityMovie($object);
        $this->updateEntitySerie($object);
        $this->updateEntitySaga($object);
        $this->updateEntityPage($object);

        $entityManager->flush();
    }

    protected function prePersistMethods(object $object, EntityManagerInterface $entityManager)
    {
        $this->initworkflow($object);
        $this->updateEntityBanIp($object, $entityManager);
        $this->updateEntityBlock($object);
        $this->updateEntityRedirection($object);
        $this->updateEntityParagraph($object);
        $this->initEntityMeta($object);
        $this->updateEntityPage($object);
    }

    protected function updateEntityBanIp(object $instance, EntityManagerInterface $entityManager): void
    {
        if (!$instance instanceof BanIp) {
            return;
        }

        $entityRepository = $this->entityManager->getRepository(HttpErrorLogs::class);
        $httpsLogs        = $entityRepository->findBy(
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

        if (PageEnum::HOME->value == $instance->getType()) {
            $instance->setPage(null);

            return;
        }

        if (in_array($instance->getType(), [PageEnum::HOME->value, PageEnum::ERRORS->value])) {
            return;
        }

        $code = (PageEnum::CV->value == $instance->getType()) ? 'head-cv' : 'head';
        $this->addParagraph($instance, $code, 0);
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
