<?php

namespace Labstag\Data;

use Labstag\Entity\Page;
use Labstag\Entity\Person;
use Labstag\Enum\PageEnum;
use Override;

class PersonData extends PageData implements DataInterface
{

    #[Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Person;
    }

    #[Override]
    public function generateSlug(object $entity): array
    {
        $page = $this->entityManager->getRepository(Page::class)->findOneBy(
            [
                'type' => PageEnum::PERSONS->value,
            ]
        );

        $slug = parent::generateSlugPage($page);
        $slug['slug'] .= '/' . $entity->getSlug();

        return $slug;
    }

    #[Override]
    public function match(?string $slug): bool
    {
        $page = $this->getEntityBySlugPerson($slug);

        return $page instanceof Person;
    }

    public function getDefaultImage(object $entity): ?string
    {
        return $entity->getProfile();
    }

    protected function getEntityBySlugPerson(?string $slug): ?object
    {
        if (0 === substr_count((string) $slug, '/')) {
            return null;
        }

        $slugSecond = basename((string) $slug);
        $slugFirst  = dirname((string) $slug);

        $page = $this->entityManager->getRepository(Page::class)->findOneBy(
            ['slug' => $slugFirst]
        );
        if (!$page instanceof Page) {
            return null;
        }

        if ($page->getType() != PageEnum::PERSONS->value) {
            return null;
        }

        return $this->entityManager->getRepository(Person::class)->findOneBy(
            ['slug' => $slugSecond]
        );
    }

    #[Override]
    public function getEntity(?string $slug): object
    {
        return $this->getEntityBySlugPerson($slug);
    }

    #[Override]
    public function supportsData(object $entity): bool
    {
        return $entity instanceof Person;
    }

    #[Override]
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('person');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return parent::placeholder();
    }
}