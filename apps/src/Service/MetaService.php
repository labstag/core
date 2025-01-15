<?php

namespace Labstag\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Meta;
use ReflectionClass;
use stdClass;
use Symfony\Component\PropertyAccess\PropertyAccess;

class MetaService
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
    )
    {
    }

    public function getEntityParent(?Meta $meta): ?object
    {
        if (!$meta instanceof Meta) {
            return null;
        }

        $return = new stdClass();

        $return->name = null;
        $return->value = null;

        $reflectionClass = new ReflectionClass($meta);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $name = $reflectionProperty->getName();
            $value = $propertyAccessor->getValue($meta, $name);
            if (!is_object($value)) {
                continue;
            }

            if ($value instanceof DateTime) {
                continue;
            }

            $return->name = $name;
            $return->value = $value;
        }

        return $return;
    }
}
