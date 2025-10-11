<?php

namespace Labstag\Twig\Runtime;

use Labstag\Entity\Paragraph;
use Labstag\Service\ParagraphService;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class ParagraphExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        protected ParagraphService $paragraphService,
        protected TranslatorInterface $translator,
    )
    {
        // Inject dependencies if needed
    }

    /**
     * @return array<string, mixed>
     */
    public function getContextMenu(Paragraph $paragraph): array
    {
        $urlAdmin = $this->paragraphService->getUrlAdmin($paragraph);
        $data     = [
            'id'    => $this->getId($paragraph),
            'class' => $this->getClass($paragraph),
        ];

        if (is_null($urlAdmin)) {
            return $data;
        }

        $data['data-context_url']  = $urlAdmin;
        $data['data-context_text'] = $this->translator->trans(
            new TranslatableMessage('Update paragraph (%type%)'),
            ['%type%' => (string) $paragraph->getType()]
        );

        return $data;
    }

    public function getFond(?string $code): ?string
    {
        return $this->paragraphService->getFond($code);
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

    private function getClass(Paragraph $paragraph): string
    {
        $tab = [
            'paragraph',
            'paragraph_' . $paragraph->getType(),
        ];

        $tab = array_merge($tab, $this->paragraphService->getClasses($paragraph));

        return trim(implode(' ', $tab));
    }

    private function getId(Paragraph $paragraph): string
    {
        return 'paragraph_' . $paragraph->getType() . '-' . $paragraph->getId();
    }
}
