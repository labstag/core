<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Paragraph;
use Labstag\Paragraph\Abstract\ParagraphLib;
use Override;

class MapParagraph extends ParagraphLib
{
    /**
     * @param mixed[] $data
     */
    #[Override]
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

    #[Override]
    public function getName(): string
    {
        return 'Map';
    }

    #[Override]
    public function getType(): string
    {
        return 'map';
    }

    /**
     * @return mixed[]
     */
    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
