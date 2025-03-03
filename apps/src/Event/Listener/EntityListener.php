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
use Labstag\Entity\Redirection;
use Labstag\Entity\Story;
use Labstag\Lib\EventEntityLib;
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
final class EntityListener extends EventEntityLib
{

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
        $this->updateEntityRedirection($object, $entityManager);

        $entityManager->flush();
    }

    public function prePersist(PrePersistEventArgs $prePersistEventArgs): void
    {
        $object        = $prePersistEventArgs->getObject();
        $prePersistEventArgs->getObjectManager();
        $this->initworkflow($object);
        $this->initEntityMeta($object);
    }
}
