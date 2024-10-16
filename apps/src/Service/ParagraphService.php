<?php

namespace Labstag\Service;

use DateTime;
use Doctrine\Common\Util\ClassUtils;
use Labstag\Entity\Paragraph;
use Labstag\Interface\ParagraphInterface;
use ReflectionClass;
use stdClass;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Form\Form;
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
            $value = $propertyAccessor->getValue($paragraph, $name);
            if (!is_object($value) || $value instanceof DateTime) {
                continue;
            }

            $class  = ClassUtils::getClass($value);
            $entity = new $class();
            if ($entity instanceof ParagraphInterface) {
                continue;
            }

            $object->name  = $name;
            $object->value = $value;

            break;
        }

        return $object;
    }

    public function getFields(Form $form, $paragraph)
    {
        if (!$paragraph instanceof Paragraph) {
            return [];
        }

        $type   = $paragraph->getType();
        $fields = [];
        foreach ($this->paragraphs as $row) {
            if ($row->getType() == $type) {
                $fields = $row->getFields($form, $paragraph);

                break;
            }
        }

        return $fields;
    }

    public function getFieldsCrudEA($paragraph)
    {
        if (!$paragraph instanceof Paragraph) {
            return [];
        }

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

    // TODO : show content
    public function showContent(Paragraph $paragraph)
    {
        unset($paragraph);
    }
}
