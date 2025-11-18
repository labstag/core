<?php

namespace Labstag\MessageHandler;

use Labstag\Message\RecommandationMovieMessage;
use Labstag\Service\FileService;
use Labstag\Service\Imdb\SerieService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RecommandationMovieMessageHandler
{
    public function __construct(
        private SerieService $serieService,
        private FileService $fileService,
    )
    {
    }

    public function __invoke(RecommandationMovieMessage $recommandationMovieMessage): void
    {
        unset($recommandationMovieMessage);
        $recommandations = $this->serieService->getAllRecommandations();

        $filename = 'recommandations-serie.json';
        $this->fileService->saveFileInAdapter(
            'private',
            $filename,
            json_encode($recommandations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
        // do something with your message
    }
}
