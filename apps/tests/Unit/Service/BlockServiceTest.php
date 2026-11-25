<?php

declare(strict_types=1);

namespace Labstag\Tests\Unit\Service;

use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Labstag\Block\BlockAbstract;
use Labstag\Controller\Admin\BlockCrudController;
use Labstag\Entity\Block;
use Labstag\Repository\BlockRepository;
use Labstag\Service\BlockService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class BlockServiceTest extends TestCase
{
    private function makeService(
        iterable $blocks = [],
        ?BlockRepository $blockRepository = null,
        ?TranslatorInterface $translator = null,
        ?Security $security = null,
        ?AuthorizationCheckerInterface $auth = null,
        ?AdminUrlGenerator $adminUrlGenerator = null,
    ): BlockService {
        return new BlockService(
            $blocks,
            $adminUrlGenerator ?? $this->createMock(AdminUrlGenerator::class),
            $security ?? $this->createMock(Security::class),
            $auth ?? $this->createMock(AuthorizationCheckerInterface::class),
            $blockRepository ?? $this->createMock(BlockRepository::class),
            $translator ?? $this->createMock(TranslatorInterface::class),
        );
    }

    #[Test]
    #[Group('unit')]
    public function getRegionsReturnsThreeRegions(): void
    {
        $service = $this->makeService();
        $this->assertSame(['header' => 'header', 'footer' => 'footer', 'main' => 'main'], $service->getRegions());
    }

    #[Test]
    #[Group('unit')]
    public function getByCodeReturnsNullWhenNoBlocks(): void
    {
        $service = $this->makeService([]);
        $this->assertNull($service->getByCode('foo'));
    }

    #[Test]
    #[Group('unit')]
    public function getClasseByCodeReturnsNullWhenNoBlocks(): void
    {
        $service = $this->makeService([]);
        $this->assertNull($service->getClasseByCode('foo'));
    }

    #[Test]
    #[Group('unit')]
    public function getAllReturnsEmptyWhenNoBlocks(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $service = $this->makeService([], translator: $translator);
        $this->assertSame([], $service->getAll(null));
    }

    #[Test]
    #[Group('unit')]
    public function getContentsReturnsStdClassWithEmptyArrays(): void
    {
        $service = $this->makeService();
        $data = $service->getContents([]);
        $this->assertInstanceOf(stdClass::class, $data);
        $this->assertSame([], $data->header);
        $this->assertSame([], $data->footer);
    }

    #[Test]
    #[Group('unit')]
    public function getFieldsReturnsEmptyWhenBlockNull(): void
    {
        $service = $this->makeService();
        $this->assertSame([], $service->getFields(null, 'page'));
    }

    #[Test]
    #[Group('unit')]
    public function getNameReturnsEmptyWhenBlockNull(): void
    {
        $service = $this->makeService();
        $this->assertSame('', $service->getName(null));
    }

    #[Test]
    #[Group('unit')]
    public function getTypeReturnsEmptyWhenBlockNull(): void
    {
        $service = $this->makeService();
        $this->assertSame('', $service->getType(null));
    }

    #[Test]
    #[Group('unit')]
    public function getBlockReturnsNullWhenNotFound(): void
    {
        $repo = $this->createMock(BlockRepository::class);
        $repo->method('find')->willReturn(null);
        $service = $this->makeService([], blockRepository: $repo);
        $this->assertNull($service->getBlock('1'));
    }

    #[Test]
    #[Group('unit')]
    public function getUrlAdminReturnsNullWhenNotGranted(): void
    {
        $security = $this->createMock(Security::class);
        $security->method('isGranted')->willReturn(false);
        $service = $this->makeService(security: $security);
        $block = $this->createMock(Block::class);
        $this->assertNull($service->getUrlAdmin($block));
    }

    #[Test]
    #[Group('unit')]
    public function contentReturnsNullWhenNoBlocks(): void
    {
        $service = $this->makeService([]);
        $block = $this->createMock(Block::class);
        $this->assertNull($service->content('view', $block));
    }

    #[Test]
    #[Group('unit')]
    public function generateReturnsEmptyWhenNoBlocks(): void
    {
        $service = $this->makeService([]);
        $this->assertSame([], $service->generate([], [], false));
    }

    #[Test]
    #[Group('unit')]
    public function updateDoesNothingWhenNoBlocks(): void
    {
        $service = $this->makeService([]);
        $block = $this->createMock(Block::class);
        $service->update($block);
        $this->assertTrue(true);
    }
}
