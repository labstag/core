<?php

namespace Labstag\Twig\Runtime;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Labstag\Entity\Casting;
use Twig\Extension\RuntimeExtensionInterface;

class CastingExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
    )
    {
        // Inject dependencies if needed
    }

    public function cast($data): mixed
    {
        $repository = $this->entityManager->getRepository(Casting::class);
        $castings = $repository->findWithActiveCastings($data);

        return $castings;
    }

    public function acting($data): array
    {
        return $this->getByType('Acting', $data);
    }

    public function writing($data): array
    {
        return $this->getByType('Writing', $data);
    }

    public function directing($data): array
    {
        return $this->getByType('Directing', $data);
    }

    public function production($data): array
    {
        return $this->getByType('Production', $data);
    }

    public function editing($data): array
    {
        return $this->getByType('Editing', $data);
    }

    private function getByType(string $type, $data): array
    {
        $casting = [];
        foreach ($data as $row) {
            if ($row->getKnownForDepartment() != $type) {
                continue;
            }
            $casting[] = $row;
        }

        return $casting;
    }
}
