<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Block;
use Labstag\Entity\ErrorParagraph as EntityErrorParagraph;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Enum\PageEnum;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class ErrorParagraph extends ParagraphAbstract implements ParagraphInterface
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Paragraph $paragraph, array $data, bool $disable): void
    {
        unset($disable);
        if (!isset($data['entity']) || !$data['entity'] instanceof Page) {
            $this->setShow($paragraph, false);

            return;
        }

        if ($data['entity']->getType() != PageEnum::ERRORS->value) {
            $this->setShow($paragraph, false);

            return;
        }

        $exception = $this->context->getException();

        $statusCode = $exception->getStatusCode();
        $title = match ($statusCode) {
            401 => new TranslatableMessage('page.error.401.title'),
            404 => new TranslatableMessage('page.error.404.title'),
            403 => new TranslatableMessage('page.error.403.title'),
            500 => new TranslatableMessage('page.error.500.title'),
            default => new TranslatableMessage('page.error.default.title'),
        };

        $message = match ($statusCode) {
            401 => new TranslatableMessage('page.error.401.message'),
            404 => new TranslatableMessage('page.error.404.message'),
            403 => new TranslatableMessage('page.error.403.message'),
            500 => new TranslatableMessage('page.error.500.message'),
            default => new TranslatableMessage('page.error.default.message'),
        };

        $data['entity']->setTitle($this->translator->trans($title));

        $this->setData(
            $paragraph,
            [
                'message'   => $this->translator->trans($message),
                'post'      => $data['entity'],
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    public function getClass(): string
    {
        return EntityErrorParagraph::class;
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Error');
    }

    #[Override]
    public function getType(): string
    {
        return 'error';
    }

    #[Override]
    public function supports(?object $object): bool
    {
        if (is_null($object)) {
            return true;
        }

        return $object instanceof Block;
    }
}
