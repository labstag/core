<?php

namespace Labstag\Service;

use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Gedmo\Tool\ClassUtils;
use Labstag\Controller\Admin\ParagraphCrudController;
use Labstag\Entity\Paragraph;
use Labstag\Repository\ParagraphRepository;
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
        #[AutowireIterator('labstag.paragraphs')]
        private readonly iterable $paragraphs,
        private AdminUrlGenerator $adminUrlGenerator,
        private ParagraphRepository $paragraphRepository,
        private Security $security,
    )
    {
    }

    public function addInPosition(object $entity, Paragraph $paragraph, int $position): void
    {
        $paragraphs = $entity->getParagraphs()->toArray();
        array_splice($paragraphs, $position, 0, [$paragraph]);
        foreach ($paragraphs as $key => $row) {
            $row->setPosition($key);
        }

        // clear the Doctrine Collection instead of calling a non-existent clearParagraphs()
        $collection = $entity->getParagraphs();
        if (method_exists($collection, 'clear')) {
            $collection->clear();
        } elseif (method_exists($entity, 'removeParagraph')) {
            // fallback: try to remove items via removeParagraph if available
            foreach ($collection as $p) {
                $entity->removeParagraph($p);
            }
        }

        foreach ($paragraphs as $row) {
            $entity->addParagraph($row);
        }
    }

    public function addParagraph(object $entity, string $type, ?int $position = null): ?Paragraph
    {
        $find = false;
        foreach ($this->paragraphs as $row) {
            if ($row->supports($entity) && $row->getType() == $type) {
                $find = true;
                break;
            }
        }

        if (!$find || !isset($row)) {
            return null;
        }

        $paragraphClass = $row->getClass();
        $paragraph      = new $paragraphClass();

        $this->addInPosition(
            $entity,
            $paragraph,
            is_null($position) ? count($entity->getParagraphs()) : $position
        );

        return $paragraph;
    }

    public function content(string $view, Paragraph $paragraph): ?Response
    {
        $content = null;

        foreach ($this->paragraphs as $row) {
            if ($paragraph::class != $row->getClass()) {
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
    public function getAll(?object $entity): array
    {
        $paragraphs = [];
        foreach ($this->paragraphs as $paragraph) {
            $name  = $paragraph->getName();
            if ($paragraph->supports($entity)) {
                $paragraphs[$name] = $paragraph->getType();
            }
        }

        ksort($paragraphs);

        return $paragraphs;
    }

    public function getByCode(?string $code): ?object
    {
        foreach ($this->paragraphs as $paragraph) {
            if ($paragraph->getType() == $code) {
                return $paragraph;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getClasses(Paragraph $paragraph): array
    {
        $classes = [];

        foreach ($this->paragraphs as $row) {
            if ($paragraph::class != $row->getClass()) {
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

        $object           = new stdClass();
        $object->name     = null;
        $object->value    = null;

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

        $fields = [];
        foreach ($this->paragraphs as $row) {
            if ($row->getClass() == $paragraph::class) {
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

    public function getName(?Paragraph $paragraph): string
    {
        if (!$paragraph instanceof Paragraph) {
            return '';
        }

        $name = '';
        foreach ($this->paragraphs as $row) {
            if ($row->getClass() == $paragraph::class) {
                $name = $row->getName();

                break;
            }
        }

        return $name;
    }

    public function getParagraph(?string $idParagraph): ?object
    {
        $paragraph  = $this->paragraphRepository->find($idParagraph);
        if (!$paragraph instanceof Paragraph) {
            return null;
        }

        foreach ($this->paragraphs as $row) {
            if ($paragraph::class != $row->getClass()) {
                continue;
            }

            return $row;
        }

        return null;
    }

    public function getType(Paragraph $paragraph): string
    {
        $type = '';
        foreach ($this->paragraphs as $row) {
            if ($paragraph::class != $row->getClass()) {
                continue;
            }

            $type = $row->getType();

            break;
        }

        return $type;
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
            if ($paragraph::class != $row->getClass()) {
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
            if ($paragraph::class != $row->getClass()) {
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
            if ($paragraph::class != $row->getClass()) {
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
            if ($paragraph::class != $row->getClass()) {
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
            if ($paragraph::class != $row->getClass()) {
                continue;
            }

            $template = $row->templates($paragraph, $type);

            break;
        }

        return $template;
    }
}
