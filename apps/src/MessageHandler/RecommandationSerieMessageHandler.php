<?php

namespace Labstag\MessageHandler;

use Labstag\Message\RecommandationSerieMessage;
use Labstag\Service\FileService;
use Labstag\Service\Imdb\MovieService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RecommandationSerieMessageHandler
{
    public function __construct(
        private MovieService $movieService,
        private FileService $fileService,
    )
    {
    }

    public function __invoke(RecommandationSerieMessage $recommandationSerieMessage): void
    {
        unset($recommandationSerieMessage);
        $recommandations = $this->movieService->getAllRecommandations();

        $filename = 'recommandations-movie.json';
        $this->fileService->saveFileInAdapter(
            'private',
            $filename,
            json_encode($recommandations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
}
