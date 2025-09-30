<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Lib\Admin\Factory\CrudFieldFactory;
use Labstag\Service\FileService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatableMessage;

final class CrudFieldFactoryTest extends TestCase
{
    private CrudFieldFactory $factory;

    protected function setUp(): void
    {
        $fileService = $this->createMock(FileService::class);
        $fileService->method('getBasePath')->willReturn('/uploads/test');
        $this->factory = new CrudFieldFactory($fileService);
    }

    public function testIdField(): void
    {
        $field = $this->factory->idField();
        self::assertInstanceOf(IdField::class, $field);
    $dto = $field->getAsDto();
    self::assertSame('id', $dto->getProperty());
    }

    public function testSlugField(): void
    {
        $field = $this->factory->slugField();
        self::assertInstanceOf(SlugField::class, $field);
    }

    public function testMetaFieldsProvideSeoTab(): void
    {
        $fields = $this->factory->metaFields();
        self::assertGreaterThanOrEqual(4, count($fields));
        $first = $fields[0];
        // Rely on string cast of TranslatableMessage here
        self::assertStringContainsString('SEO', (string) $first->getAsDto()->getLabel());
    }

    public function testBaseIdentitySetIncludesEnableAndTitle(): void
    {
        $fields = $this->factory->baseIdentitySet('post', 'index', 'Labstag\\Entity\\Post');
    $properties = array_map(fn($f) => $f->getAsDto()->getProperty(), $fields);
        self::assertContains('enable', $properties);
        self::assertContains('title', $properties);
    }

    public function testFullContentSetMergesParagraphMetaAndRefUser(): void
    {
        $set = $this->factory->fullContentSet('post', 'index', 'Labstag\\Entity\\Post', true);
        // Should include id and meta.title and tags
        $props = array_map(fn($f) => $f->getAsDto()->getProperty(), array_filter($set, fn($f) => method_exists($f, 'getAsDto')));
        self::assertContains('id', $props);
        self::assertContains('meta.title', $props);
        self::assertContains('tags', $props);
    }

    public function testDateSetReturnsTabAndTwoDates(): void
    {
        $dateSet = $this->factory->dateSet();
        self::assertCount(3, $dateSet); // tab + created + updated
    }

    public function testImageFieldSwitchesBetweenTextAndImage(): void
    {
        $fieldNew = $this->factory->imageField('img', 'new', 'Labstag\\Entity\\Post');
        $fieldIndex = $this->factory->imageField('img', 'index', 'Labstag\\Entity\\Post');
        self::assertSame('imgFile', $fieldNew->getAsDto()->getProperty());
        self::assertSame('img', $fieldIndex->getAsDto()->getProperty());
    }
}
