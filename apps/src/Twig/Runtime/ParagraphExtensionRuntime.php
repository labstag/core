<?php

namespace Labstag\Twig\Runtime;

use Labstag\Entity\Paragraph;
use Labstag\Service\ParagraphService;
use Twig\Extension\RuntimeExtensionInterface;

class ParagraphExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        protected ParagraphService $paragraphService,
    )
    {
        // Inject dependencies if needed
    }

    public function getClass(Paragraph $paragraph): string
    {
        return 'paragraph_' . $paragraph->getType();
    }

    public function getFond(?string $code): ?string
    {
        return $this->paragraphService->getFond($code);
    }

    public function getId(Paragraph $paragraph): string
    {
        return 'paragraph_' . $paragraph->getType() . '-' . $paragraph->getId();
    }

    public function getName(string $code): string
    {
        return $this->paragraphService->getNameByCode($code);
    }

    /**
     * @param mixed[] $tab
     */
    public function getShow(array $tab): ?string
    {
        if (!isset($tab['templates']['view']) || !isset($tab['paragraph'])) {
            return null;
        }

        $content = $this->paragraphService->content($tab['templates']['view'], $tab['paragraph']);

        if (is_null($content)) {
            return null;
        }

        return $content->getContent();
    }
}
