<?php

namespace Labstag\Service;

use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Gedmo\Tool\ClassUtils;
use Labstag\Controller\Admin\ParagraphCrudController;
use Labstag\Entity\Paragraph;
use ReflectionClass;
use stdClass;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;

final class ParagraphService
{

    private array $init = [];

    public function __construct(
        /**
         * @var iterable<\Labstag\Paragraph\Abstract\ParagraphLib>
         */
        #[AutowireIterator('labstag.paragraphs')]
        private readonly iterable $paragraphs,
        private AdminUrlGenerator $adminUrlGenerator,
        private Security $security,
    )
    {
    }

    public function addParagraph(object $entity, string $type): ?Paragraph
    {
        $paragraph = null;
        $all       = $this->getAll($entity::class);
        $position  = count($entity->getParagraphs());
        foreach ($all as $row) {
            if ($row != $type) {
                continue;
            }

            $paragraph = new Paragraph();
            $paragraph->setType($type);
            $paragraph->setPosition($position);
            $entity->addParagraph($paragraph);

            break;
        }

        return $paragraph;
    }

    public function content(string $view, Paragraph $paragraph): ?Response
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

    /**
     * @param mixed[] $paragraphs
     * @param mixed[] $data
     *
     * @return array{templates: mixed, paragraph: mixed}[]
     */
    public function generate(array $paragraphs, array $data, bool $disable): array
    {
        $tab = [];
        foreach ($paragraphs as $paragraph) {
            $this->setContents($paragraph, $data, $disable);

            $tab[] = [
                'templates' => $this->templates($paragraph, 'content'),
                'paragraph' => $paragraph,
            ];
        }

        return $tab;
    }

    /**
     * @return mixed[]
     */
    public function getAll(?string $entity): array
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

    /**
     * @return array<string, mixed>
     */
    public function getClasses(Paragraph $paragraph): array
    {
        $classes = [];

        foreach ($this->paragraphs as $row) {
            if ($paragraph->getType() != $row->getType()) {
                continue;
            }

            $classes = $row->getClasses($paragraph);

            break;
        }

        return $classes;
    }

    /**
     * @param mixed[] $paragraphs
     */
    public function getContents(array $paragraphs): stdClass
    {
        $data         = new stdClass();
        $data->header = [];
        $data->footer = [];
        foreach ($paragraphs as $paragraph) {
            $header = $this->getHeader($paragraph['paragraph']);
            if (is_array($header)) {
                $data->header = array_merge($data->header, $header);
            } elseif ($header instanceof Response) {
                $data->header[] = $header;
            }

            $footer = $this->getFooter($paragraph['paragraph']);
            if (is_array($footer)) {
                $data->footer = array_merge($data->footer, $footer);
            } elseif ($footer instanceof Response) {
                $data->footer[] = $footer;
            }
        }

        $data->header = array_filter($data->header, fn ($row): bool => !is_null($row));
        $data->footer = array_filter($data->footer, fn ($row): bool => !is_null($row));

        return $data;
    }

    public function getEntityParent(?Paragraph $paragraph): ?object
    {
        if (!$paragraph instanceof Paragraph) {
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
            if (!is_object($value)) {
                continue;
            }

            if ($value instanceof DateTime) {
                continue;
            }

            $class = ClassUtils::getClass($value);
            if (!str_contains($class, 'Labstag\Entity')) {
                continue;
            }

            $object->name  = $name;
            $object->value = $value;

            break;
        }

        return $object;
    }

    public function getFields(mixed $paragraph, string $pageName): mixed
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

    public function getFond(?string $info): ?string
    {
        if (is_null($info)) {
            return null;
        }

        $fonds = array_flip($this->getFonds());

        return $fonds[$info] ?? null;
    }

    // TODO : add fonds

    /**
     * @return mixed[]
     */
    public function getFonds(): array
    {
        return [];
    }

    public function getNameByCode(string $code): string
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

    public function getUrlAdmin(Paragraph $paragraph): ?AdminUrlGeneratorInterface
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return null;
        }

        $adminUrlGenerator = $this->adminUrlGenerator->setAction(Action::EDIT);
        $adminUrlGenerator->setEntityId($paragraph->getId());

        return $adminUrlGenerator->setController(ParagraphCrudController::class);
    }

    public function update(Paragraph $paragraph): void
    {
        foreach ($this->paragraphs as $row) {
            if ($paragraph->getType() != $row->getType()) {
                continue;
            }

            $row->update($paragraph);

            break;
        }
    }

    private function getFooter(Paragraph $paragraph): mixed
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

    private function getHeader(Paragraph $paragraph): mixed
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

    /**
     * @param mixed[] $data
     */
    private function setContents(?Paragraph $paragraph, array $data, bool $disable): void
    {
        if (!$paragraph instanceof Paragraph) {
            return;
        }

        foreach ($this->paragraphs as $row) {
            if ($paragraph->getType() != $row->getType()) {
                continue;
            }

            if (isset($this->init[$paragraph->getId()])) {
                return;
            }

            $this->init[$paragraph->getId()] = true;

            $row->generate($paragraph, $data, $disable);

            break;
        }
    }

    /**
     * @return mixed[]|null
     */
    private function templates(Paragraph $paragraph, string $type): ?array
    {
        $template = null;
        foreach ($this->paragraphs as $row) {
            if ($paragraph->getType() != $row->getType()) {
                continue;
            }

            $template = $row->templates($paragraph, $type);

            break;
        }

        return $template;
    }
}
