<?php

namespace Labstag\Service;

use DateTime;
use Labstag\Entity\Paragraph;
use ReflectionClass;
use stdClass;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ParagraphService
{
    public function __construct(
        #[AutowireIterator('labstag.paragraphs')]
        private readonly iterable $paragraphs
    )
    {
    }

    public function getAll($entity): array
    {
        $paragraphs = [];
        foreach ($this->paragraphs as $paragraph) {
            $inUse = $paragraph->useIn();
            $type  = $paragraph->getType();
            $name  = $paragraph->getName();
            if ((in_array($entity, $inUse) && $paragraph->isEnable()) || is_null($entity)) {
                $paragraphs[$name] = $type;
            }
        }

        return $paragraphs;
    }

    public function getEntityParent(?Paragraph $paragraph): ?object
    {
        if (is_null($paragraph)) {
            return null;
        }

        $object        = new stdClass();
        $object->name  = null;
        $object->value = null;

        $reflectionClass  = new ReflectionClass($paragraph);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $name  = $reflectionProperty->getName();
            $type  = $reflectionProperty->getType();
            $value = $propertyAccessor->getValue($paragraph, $name);
            if (!is_object($value) || $value instanceof DateTime) {
                continue;
            }

            dump($name, $type, $value);
        }

        return $object;
    }

    public function getFields(Paragraph $paragraph)
    {
        $type   = $paragraph->getType();
        $fields = [];
        foreach ($this->paragraphs as $row) {
            if ($row->getType() == $type) {
                $fields = $row->getFields($paragraph);

                break;
            }
        }

        return $fields;
    }

    public function getFond($info)
    {
        $fonds = array_flip($this->getFonds());

        return $fonds[$info] ?? null;
    }

    // TODO : add fonds
    public function getFonds(): array
    {
        return [];
    }

    public function getNameByCode($code)
    {
        $name = '';
        foreach ($this->paragraphs as $paragraph) {
            if ($paragraph->getType() == $code) {
                $name = $paragraph->getName();

                break;
            }
        }

        return $name;
    }

    public function getTypeEntity(Paragraph $paragraph)
    {
        $type      = $paragraph->getType();
        $paragraph = null;
        foreach ($this->paragraphs as $row) {
            if ($row->getType() == $type) {
                $paragraph = $row->getEntity();

                break;
            }
        }

        return $paragraph;
    }

    // TODO : show content
    public function showContent(Paragraph $paragraph)
    {
        unset($paragraph);
    }
}
