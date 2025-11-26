<?php

namespace Labstag\Service\Igdb;

use Exception;
use Labstag\Entity\Platform;
use Symfony\Component\HttpFoundation\Request;

final class PlatformService extends AbstractIgdb
{
    public function addByApi(string $id): ?Platform
    {
        $result = $this->getApiPlatformId($id);
        if (is_null($result)) {
            return null;
        }

        $platform = $this->getPlatform($result);
        $this->update($platform);

        $this->entityManager->getRepository(Platform::class)->save($platform);

        return $platform;
    }

    public function getPlatform(array $data): Platform
    {
        $entityRepository = $this->entityManager->getRepository(Platform::class);
        $platform         = $entityRepository->findOneBy(
            [
                'igdb' => $data['id'],
            ]
        );
        if ($platform instanceof Platform) {
            return $platform;
        }

        $platform = new Platform();
        $platform->setIgdb($data['id']);
        $platform->setAbbreviation($data['abbreviation'] ?? '');
        $platform->setGeneration($data['generation'] ?? 0);
        $platform->setTitle($data['name']);

        $resultFamily = $this->getApiPlatformFamilyId($data['platform_family'] ?? '0');
        if (!is_null($resultFamily)) {
            $platform->setFamily($resultFamily['name']);
        }

        return $platform;
    }

    public function getPlatformApi(Request $request): array
    {
        $entityRepository = $this->entityManager->getRepository(Platform::class);
        $igbds              = $entityRepository->getAllIgdb();
        $all                = $request->query->all();
        $platforms          = [];
        if (isset($all['platform'])) {
            $search    = $all['platform']['title'] ?? '';
            $body      = $this->igdbApi->setBody(search: $search, limit: 20);
            $platforms = $this->igdbApi->setUrl('platforms', $body);
            if (is_null($platforms)) {
                $platforms = [];
            }
        }

        return array_filter($platforms, fn (array $platform): bool => !in_array($platform['id'], $igbds));
    }

    public function update(Platform $platform): ?bool
    {
        $result = $this->getApiPlatformId($platform->getIgdb() ?? '0');
        if (0 == count($result)) {
            return null;
        }

        $statuses = [$this->updateImage($platform, $result)];

        return in_array(true, $statuses, true);
    }

    private function getApiPlatformFamilyId(string $id): ?array
    {
        $body = $this->igdbApi->setBody(where: 'id = ' . $id, limit: 1);

        $results = $this->igdbApi->setUrl('platform_families', $body);
        if (is_null($results)) {
            return null;
        }

        return $results[0];
    }

    private function getApiPlatformId(string $id): ?array
    {
        $body = $this->igdbApi->setBody(where: 'id = ' . $id, limit: 1);

        $results = $this->igdbApi->setUrl('platforms', $body);
        if (is_null($results)) {
            return null;
        }

        return $results[0];
    }

    private function getApiPlatformLogosId(array $data): ?array
    {
        $body = $this->igdbApi->setBody(where: 'id = ' . $data['platform_logo'], limit: 1);

        $results = $this->igdbApi->setUrl('platform_logos', $body);
        if (is_null($results)) {
            return null;
        }

        return $results;
    }

    private function updateImage(Platform $platform, array $data): bool
    {
        $result = $this->getApiPlatformLogosId($data);
        if (is_null($result)) {
            return false;
        }

        $imageUrl = $this->igdbApi->buildImageUrl($result[0]['image_id'], 'original');
        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'poster_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents($tempPath, file_get_contents($imageUrl));
            $this->fileService->setUploadedFile($tempPath, $platform, 'imgFile');

            return true;
        } catch (Exception) {
            return false;
        }
    }
}
