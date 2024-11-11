<?php

namespace Labstag\Twig\Runtime;

use Labstag\Entity\Paragraph;
use Labstag\Service\ParagraphService;
use Twig\Extension\RuntimeExtensionInterface;

class ParagraphExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        protected ParagraphService $paragraphService
    )
    {
        // Inject dependencies if needed
    }

    public function getClass(Paragraph $paragraph): string
    {
        return $paragraph->getType();
    }

    public function getFond($code)
    {
        return $this->paragraphService->getFond($code);
    }

    public function getId(Paragraph $paragraph): string
    {
        return $paragraph->getType().'-'.$paragraph->getId();
    }

    public function getName($code)
    {
        return $this->paragraphService->getNameByCode($code);
    }

    public function getShow($tab)
    {
        if (!isset($tab['templates']['view'])) {
            return null;
        }

        $content = $this->paragraphService->content(
            $tab['templates']['view'],
            $tab['paragraph']
        );

        if (is_null($content)) {
            return null;
        }

        return $content->getContent();
    }
}
