<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Labstag\Entity\Paragraph;
use Labstag\Repository\ParagraphRepository;
use Labstag\Service\ParagraphService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ParagraphServiceTest extends TestCase
{
    #[Test]
    #[Group('unit')]
    public function contentReturnsNullWhenNoParagraphs(): void
    {
        $service   = $this->makeService();
        $paragraph = $this->createStub(Paragraph::class);
        $this->assertNull($service->content('view', $paragraph));
    }

    #[Test]
    #[Group('unit')]
    public function generateReturnsEmptyWhenNoParagraphs(): void
    {
        $service = $this->makeService();
        $this->assertSame([], $service->generate([], [], false));
    }

    #[Test]
    #[Group('unit')]
    public function getByCodeReturnsNullWhenNoParagraphs(): void
    {
        $service = $this->makeService();
        $this->assertNull($service->getByCode('foo'));
    }

    #[Test]
    #[Group('unit')]
    public function getContentsReturnsStdClassWithEmptyArrays(): void
    {
        $service = $this->makeService();
        $data    = $service->getContents([]);
        $this->assertIsObject($data);
        $this->assertSame([], $data->header);
        $this->assertSame([], $data->footer);
    }

    #[Test]
    #[Group('unit')]
    public function getEntityParentReturnsNullWhenParagraphNull(): void
    {
        $service = $this->makeService();
        $this->assertNull($service->getEntityParent(null));
    }

    #[Test]
    #[Group('unit')]
    public function getFieldsReturnsEmptyWhenNotParagraph(): void
    {
        $service = $this->makeService();
        $this->assertSame([], $service->getFields(null, 'page'));
    }

    #[Test]
    #[Group('unit')]
    public function getFondReturnsNullWhenInfoNull(): void
    {
        $service = $this->makeService();
        $this->assertNull($service->getFond(null));
    }

    #[Test]
    #[Group('unit')]
    public function getFondsReturnsEmptyArray(): void
    {
        $service = $this->makeService();
        $this->assertSame([], $service->getFonds());
    }

    #[Test]
    #[Group('unit')]
    public function getNameReturnsEmptyWhenParagraphNull(): void
    {
        $service = $this->makeService();
        $this->assertSame('', $service->getName(null));
    }

    private function makeService(): ParagraphService
    {
        return new ParagraphService(
            [],
            $this->createStub(AdminUrlGeneratorInterface::class),
            $this->createStub(ParagraphRepository::class),
            $this->createStub(TranslatorInterface::class),
            $this->createStub(Security::class),
        );
    }
}
