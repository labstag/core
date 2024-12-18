<?php

namespace Labstag\Service;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Meta;
use ReflectionClass;
use stdClass;
use Symfony\Component\PropertyAccess\PropertyAccess;

class MetaService
{
    public function __construct(
        protected EntityManagerInterface $entityManager
    )
    {
    }

    public function getEntityParent(?Meta $meta): ?object
    {
        if (is_null($meta)) {
            return null;
        }

        $return = new stdClass();

        $return->name  = null;
        $return->value = null;

        $reflectionClass  = new ReflectionClass($meta);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $name  = $reflectionProperty->getName();
            $type  = $reflectionProperty->getType();
            $value = $propertyAccessor->getValue($meta, $name);
            if (is_null($type) || is_null($value) || !is_object($value)) {
                continue;
            }

            $return->name  = $name;
            $return->value = $value;
        }

        return $return;
    }
}
