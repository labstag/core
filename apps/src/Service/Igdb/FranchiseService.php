<?php

namespace Labstag\Service\Igdb;

use Labstag\Entity\Franchise;

final class FranchiseService extends AbstractIgdb
{
    public function addByApi(string $id): ?Franchise
    {
        $result = $this->getApiFranchiseId($id);
        if (is_null($result)) {
            return null;
        }

        $franchise = $this->getFranchise($result);
        $this->update($franchise);

        $this->entityManager->getRepository(Franchise::class)->save($franchise);

        return $franchise;
    }

    public function getFranchise(array $data): Franchise
    {
        $entityRepository = $this->entityManager->getRepository(Franchise::class);
        $franchise        = $entityRepository->findOneBy(
            [
                'igdb' => $data['id'],
            ]
        );
        if ($franchise instanceof Franchise) {
            return $franchise;
        }

        $franchise = new Franchise();
        $franchise->setTitle($data['name']);
        $franchise->setIgdb($data['id']);

        return $franchise;
    }

    public function update(Franchise $franchise): bool
    {
        $result = $this->getApiFranchiseId($franchise->getIgdb() ?? '0');

        return 0 != count($result);
    }

    private function getApiFranchiseId(string $id): ?array
    {
        $body = $this->igdbApi->setBody(where: 'id = ' . $id, limit: 1);

        $results = $this->igdbApi->setUrl('franchises', $body);
        if (is_null($results)) {
            return null;
        }

        return $results[0];
    }
}
