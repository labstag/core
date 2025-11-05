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
            [
                '%type%' => $this->paragraphService->getType($paragraph),
            ]
        );

        return $data;
    }

    public function getFond(?string $code): ?string
    {
        return $this->paragraphService->getFond($code);
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

    public function name(object $object): string
    {
        if (!$object instanceof Paragraph) {
            return '';
        }

        return $this->paragraphService->getName($object);
    }

    public function type(object $object): string
    {
        if (!$object instanceof Paragraph) {
            return '';
        }

        return $this->paragraphService->getType($object);
    }

    private function getClass(Paragraph $paragraph): string
    {
        $type = $this->paragraphService->getType($paragraph);
        $tab  = [
            'paragraph',
            'paragraph_' . $type,
        ];

        $tab = array_merge($tab, $this->paragraphService->getClasses($paragraph));

        return trim(implode(' ', $tab));
    }

    private function getId(Paragraph $paragraph): string
    {
        $type = $this->paragraphService->getType($paragraph);

        return 'paragraph_' . $type . '-' . $paragraph->getId();
    }
}
