<?php

namespace Labstag\Service;

use DateTime;
use Doctrine\Common\Util\ClassUtils;
use Labstag\Entity\Paragraph;
use Labstag\Interface\ParagraphInterface;
use ReflectionClass;
use stdClass;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ParagraphService
{
    public function __construct(
        #[AutowireIterator('labstag.paragraphs')]
        private readonly iterable $paragraphs
    )
    {
    }

    public function addParagraph($entity, $type): ?Paragraph
    {
        $paragraph = null;
        $all       = $this->getAll($entity::class);
        foreach ($all as $row) {
            if ($row != $type) {
                continue;
            }

            $paragraph = new Paragraph();
            $paragraph->setType($type);
            $entity->addParagraph($paragraph);

            break;
        }

        return $paragraph;
    }

    public function content(
        string $view,
        Paragraph $paragraph
    )
    {
        $content = null;

        foreach ($this->paragraphs as $row) {
            if ($paragraph->getType() != $row->getType()) {
                continue;
            }

            $content = $row->content($view, $paragraph);

            break;
        }

        return $content;
    }

    public function generate(array $paragraphs, array $data)
    {
        $tab = [];
        foreach ($paragraphs as $paragraph) {
            $this->setContents($paragraph, $data);

            $tab[] = [
                'templates' => $this->templates('content', $paragraph),
                'paragraph' => $paragraph,
            ];
        }

        return $tab;
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

        ksort($paragraphs);

        return $paragraphs;
    }

    public function getContents($paragraphs)
    {
        $data         = new stdClass();
        $data->header = [];
        $data->footer = [];
        foreach ($paragraphs as $paragraph) {
            $header = $this->getHeader($paragraph['paragraph']);
            $footer = $this->getFooter($paragraph['paragraph']);
            if (is_array($header)) {
                $data->header = array_merge($data->header, $header);
            } elseif ($header instanceof Response) {
                $data->header[] = $header;
            }

            if (is_array($footer)) {
                $data->footer = array_merge($data->footer, $footer);
            } elseif ($footer instanceof Response) {
                $data->footer[] = $footer;
            }
        }

        $data->header = array_filter(
            $data->header,
            fn ($row) => !is_null($row)
        );

        $data->footer = array_filter(
            $data->footer,
            fn ($row) => !is_null($row)
        );

        return $data;
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

            $class = ClassUtils::getClass($value);
            if (!str_contains($class, 'Labstag\Entity')) {
                continue;
            }

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

    public function getFields($paragraph, $pageName): iterable
    {
        if (!$paragraph instanceof Paragraph) {
            return [];
        }

        $type   = $paragraph->getType();
        $fields = [];
        foreach ($this->paragraphs as $row) {
            if ($row->getType() == $type) {
                $fields = $row->getFields($paragraph, $pageName);

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

    public function getFooter(
        Paragraph $paragraph
    )
    {
        $footer = null;

        foreach ($this->paragraphs as $row) {
            if ($paragraph->getType() != $row->getType()) {
                continue;
            }

            $footer = $row->getFooter($paragraph);

            break;
        }

        return $footer;
    }

    public function getHeader(
        Paragraph $paragraph
    )
    {
        $header = null;

        foreach ($this->paragraphs as $row) {
            if ($paragraph->getType() != $row->getType()) {
                continue;
            }

            $header = $row->getHeader($paragraph);

            break;
        }

        return $header;
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

    public function setContents(
        ?Paragraph $paragraph,
        array $data
    )
    {
        if (is_null($paragraph)) {
            return;
        }

        foreach ($this->paragraphs as $row) {
            if ($paragraph->getType() != $row->getType()) {
                continue;
            }

            $row->generate($paragraph, $data);

            break;
        }
    }

    private function templates(string $type, Paragraph $paragraph)
    {
        $template = null;
        foreach ($this->paragraphs as $row) {
            if ($paragraph->getType() != $row->getType()) {
                continue;
            }

            $template = $row->templates($type);

            break;
        }

        return $template;
    }
}
