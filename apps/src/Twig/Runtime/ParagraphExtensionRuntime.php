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

    public function getShow($tab, $data)
    {
        if (!isset($tab['templates']['view'])) {
            return null;
        }

        $content = $this->paragraphService->showContent(
            $tab['templates']['view'],
            $tab['paragraph'],
            $data
        );

        if (is_null($content)) {
            return null;
        }

        return $content->getContent();
    }
}
