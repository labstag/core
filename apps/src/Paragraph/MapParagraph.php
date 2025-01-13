<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;

class MapParagraph extends ParagraphLib
{
    #[\Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        $this->setData(
            $paragraph,
            [
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    #[\Override]
    public function getFields(Paragraph $paragraph, string $pageName): iterable
    {
        unset($paragraph, $pageName);

        return [];
    }

    #[\Override]
    public function getName(): string
    {
        return 'Map';
    }

    #[\Override

    ]
    public function getType(): string
    {
        return 'map';
    }

    #[\Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
