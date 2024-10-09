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

    public function getId($data): string
    {
        $paragraph = $data->getParagraph();

        return $paragraph->getType().'-'.$paragraph->getId();
    }

    public function getName($code)
    {
        return $this->paragraphService->getNameByCode($code);
    }

    public function getShow(Paragraph $paragraph)
    {
        $content = $this->paragraphService->showContent($paragraph);

        if (is_null($content)) {
            return null;
        }

        return $content->getContent();
    }
}
