<?php

declare(strict_types=1);

namespace Labstag\Doctrine\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Override;

class DeleteFileFilter extends SQLFilter
{
    #[Override]
    public function addFilterConstraint(ClassMetadata $classMetadata, string $targetTableAlias): string
    {
        unset($classMetadata, $targetTableAlias);

        return '';
    }
}
