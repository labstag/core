<?php

namespace Labstag\Service\Igdb;

use Labstag\Entity\Platform;

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

        $resultFamily = $this->getApiPlatformFamilyId($data['platform_family']['id'] ?? '0');
        if (!is_null($resultFamily)) {
            $platform->setFamily($resultFamily['name']);
        }

        return $platform;
    }

    public function getPlatformApi(array $data, int $limit, int $offset): array
    {
        $entityRepository   = $this->entityManager->getRepository(Platform::class);
        $igbds              = $entityRepository->getAllIgdb();
        $platforms          = [];
        $search             = $data['title'] ?? '';
        $where              = [];
        if (isset($data['family']) && !empty($data['family'])) {
            $family = $data['family'];

            $where[] = 'platform_family.name ~ "' . $family . '"';
        }

        $fields    = [
            '*',
            'platform_family.*',
        ];
        $body      = $this->igdbApi->setBody(
            search: $search,
            fields: $fields,
            where: $where,
            limit: $limit,
            offset: $offset
        );
        $platforms = $this->igdbApi->setUrl('platforms', $body);
        if (is_null($platforms)) {
            $platforms = [];
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
        $where = ['id = ' . $id];
        $body  = $this->igdbApi->setBody(where: $where, limit: 1);

        $results = $this->igdbApi->setUrl('platform_families', $body);
        if (is_null($results)) {
            return null;
        }

        return $results[0];
    }

    private function getApiPlatformId(string $id): ?array
    {
        $where  = ['id = ' . $id];
        $fields = [
            '*',
            'platform_logo.*',
            'platform_family.*',
        ];
        $body   = $this->igdbApi->setBody(fields: $fields, where: $where, limit: 1);

        $results = $this->igdbApi->setUrl('platforms', $body);
        if (is_null($results)) {
            return null;
        }

        return $results[0];
    }

    private function updateImage(Platform $platform, array $data): bool
    {
        if (!isset($data['platform_logo']['image_id']) || empty($data['platform_logo']['image_id'])) {
            return false;
        }

        $imageUrl = $this->igdbApi->buildImageUrl($data['platform_logo']['image_id'], 'original');
        $this->fileService->setUploadedFile($imageUrl, $platform, 'imgFile');

        return true;
    }
}
