<?php

namespace Labstag\MessageHandler;

use Labstag\Entity\Star;
use Labstag\Message\StarMessage;
use Labstag\Repository\StarRepository;
use Labstag\Service\FileService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class StarMessageHandler
{
    public function __construct(
        private FileService $fileService,
        private StarRepository $starRepository,
    )
    {
    }

    public function __invoke(StarMessage $starMessage): void
    {
        $data = $starMessage->getData();

        $star = new Star();
        $star->setTitle($data['name']);
        $star->setLanguage($data['language']);
        $star->setEnable(true);
        $star->setRepository($data['git_url']);
        $star->setForks($data['forks_count']);
        $star->setUrl($data['html_url']);
        $star->setDescription($data['description'] ?? null);
        $star->setLicense($data['license']['name'] ?? null);
        $star->setStargazers($data['stargazers_count'] ?? 0);
        $star->setWatchers($data['watchers_count'] ?? 0);
        $star->setOwner($data['owner']['login']);
        $this->setImage($star, $data);

        $this->starRepository->save($star);
        // do something with your message
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setImage(Star $star, array $data): bool
    {
        if (!isset($data['owner']['avatar_url'])) {
            return false;
        }

        $this->fileService->setUploadedFile($data['owner']['avatar_url'], $star, 'imgFile');

        return true;
    }
}
