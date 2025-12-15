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

        $message = $this->getMessage($statusCode);
        $data['entity']->setTitle($this->translator->trans($message->getMessage(), $message->getParameters()));
        $message = $this->getMessage($statusCode);

        $this->setData(
            $paragraph,
            [
                'trace'     => $exception->getTraceAsString(),
                'message'   => $this->translator->trans($message->getMessage(), $message->getParameters()),
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

    public function getMessage(string $statusCode): TranslatableMessage
    {
        $key = sprintf('page.error.%s.message', $statusCode);

        return new TranslatableMessage($key);
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Error');
    }

    public function getTitle(string $statusCode): TranslatableMessage
    {
        $key = sprintf('page.error.%s.title', $statusCode);

        return new TranslatableMessage($key);
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
