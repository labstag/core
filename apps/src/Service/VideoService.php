<?php

namespace Labstag\Service;

use Psr\Log\LoggerInterface;

class VideoService
{
    public function __construct(
        protected LoggerInterface $logger,
    )
    {
    }

    public function getTrailer(?array $details): ?string
    {
        if (!isset($details['results'])) {
            return null;
        }

        $video = null;
        foreach ($details['results'] as $result) {
            if ('Trailer' == $result['type']) {
                $video = $this->setVideo($result);
                if (!is_null($video)) {
                    break;
                }
            }
        }

        if (is_null($video) && 'Trailer' == $details['results'][0]['type']) {
            return $this->setVideo($details['results'][0]);
        }

        return $video;
    }

    private function setVideo(array $data)
    {
        $type  = strtolower((string) $data['site']);
        $video = match ($type) {
            'youtube' => 'https://www.youtube.com/watch?v=' . $data['key'],
            'vimeo'   => 'https://vimeo.com/' . $data['key'],
            default   => null,
        };

        if (is_null($video)) {
            $this->logger->warning('Unsupported video site', $data);
        }

        return $video;
    }
}
