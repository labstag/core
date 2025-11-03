<?php

namespace Labstag\MessageHandler;

use Exception;
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
    private function setImage(Star $star, array $data): void
    {
        if (!isset($data['owner']['avatar_url'])) {
            return;
        }

        try {
            $file     = $data['owner']['avatar_url'];
            $tempPath = tempnam(sys_get_temp_dir(), 'star_');
            file_put_contents($tempPath, file_get_contents($file));
            $this->fileService->setUploadedFile($tempPath, $star, 'imgFile');
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }
}
