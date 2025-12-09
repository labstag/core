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

        $data['entity']->setTitle($this->translator->trans($this->getTitle($statusCode)));

        $this->setData(
            $paragraph,
            [
                'trace'     => $exception->getTraceAsString(),
                'message'   => $this->translator->trans($this->getMessage($statusCode)),
                'post'      => $data['entity'],
                'paragraph' => $paragraph,
                'data'      => $data,
            ]
        );
    }

    public function getTitle($statusCode): string
    {
        return match ($statusCode) {
            400 => new TranslatableMessage('page.error.400.title'),
            401 => new TranslatableMessage('page.error.401.title'),
            402 => new TranslatableMessage('page.error.402.title'),
            403 => new TranslatableMessage('page.error.403.title'),
            404 => new TranslatableMessage('page.error.404.title'),
            405 => new TranslatableMessage('page.error.405.title'),
            406 => new TranslatableMessage('page.error.406.title'),
            407 => new TranslatableMessage('page.error.407.title'),
            408 => new TranslatableMessage('page.error.408.title'),
            409 => new TranslatableMessage('page.error.409.title'),
            410 => new TranslatableMessage('page.error.410.title'),
            411 => new TranslatableMessage('page.error.411.title'),
            412 => new TranslatableMessage('page.error.412.title'),
            413 => new TranslatableMessage('page.error.413.title'),
            414 => new TranslatableMessage('page.error.414.title'),
            415 => new TranslatableMessage('page.error.415.title'),
            416 => new TranslatableMessage('page.error.416.title'),
            417 => new TranslatableMessage('page.error.417.title'),
            418 => new TranslatableMessage('page.error.418.title'),
            421 => new TranslatableMessage('page.error.421.title'),
            422 => new TranslatableMessage('page.error.422.title'),
            423 => new TranslatableMessage('page.error.423.title'),
            424 => new TranslatableMessage('page.error.424.title'),
            425 => new TranslatableMessage('page.error.425.title'),
            426 => new TranslatableMessage('page.error.426.title'),
            428 => new TranslatableMessage('page.error.428.title'),
            429 => new TranslatableMessage('page.error.429.title'),
            431 => new TranslatableMessage('page.error.431.title'),
            451 => new TranslatableMessage('page.error.451.title'),
            500 => new TranslatableMessage('page.error.500.title'),
            501 => new TranslatableMessage('page.error.501.title'),
            502 => new TranslatableMessage('page.error.502.title'),
            503 => new TranslatableMessage('page.error.503.title'),
            504 => new TranslatableMessage('page.error.504.title'),
            505 => new TranslatableMessage('page.error.505.title'),
            506 => new TranslatableMessage('page.error.506.title'),
            507 => new TranslatableMessage('page.error.507.title'),
            508 => new TranslatableMessage('page.error.508.title'),
            510 => new TranslatableMessage('page.error.510.title'),
            511 => new TranslatableMessage('page.error.511.title'),
            default => new TranslatableMessage('page.error.default.title'),
        };
    }

    public function getMessage($statusCode): string
    {
        return  match ($statusCode) {
            400 => new TranslatableMessage('page.error.400.message'),
            401 => new TranslatableMessage('page.error.401.message'),
            402 => new TranslatableMessage('page.error.402.message'),
            403 => new TranslatableMessage('page.error.403.message'),
            404 => new TranslatableMessage('page.error.404.message'),
            405 => new TranslatableMessage('page.error.405.message'),
            406 => new TranslatableMessage('page.error.406.message'),
            407 => new TranslatableMessage('page.error.407.message'),
            408 => new TranslatableMessage('page.error.408.message'),
            409 => new TranslatableMessage('page.error.409.message'),
            410 => new TranslatableMessage('page.error.410.message'),
            411 => new TranslatableMessage('page.error.411.message'),
            412 => new TranslatableMessage('page.error.412.message'),
            413 => new TranslatableMessage('page.error.413.message'),
            414 => new TranslatableMessage('page.error.414.message'),
            415 => new TranslatableMessage('page.error.415.message'),
            416 => new TranslatableMessage('page.error.416.message'),
            417 => new TranslatableMessage('page.error.417.message'),
            418 => new TranslatableMessage('page.error.418.message'),
            421 => new TranslatableMessage('page.error.421.message'),
            422 => new TranslatableMessage('page.error.422.message'),
            423 => new TranslatableMessage('page.error.423.message'),
            424 => new TranslatableMessage('page.error.424.message'),
            425 => new TranslatableMessage('page.error.425.message'),
            426 => new TranslatableMessage('page.error.426.message'),
            428 => new TranslatableMessage('page.error.428.message'),
            429 => new TranslatableMessage('page.error.429.message'),
            431 => new TranslatableMessage('page.error.431.message'),
            451 => new TranslatableMessage('page.error.451.message'),
            500 => new TranslatableMessage('page.error.500.message'),
            501 => new TranslatableMessage('page.error.501.message'),
            502 => new TranslatableMessage('page.error.502.message'),
            503 => new TranslatableMessage('page.error.503.message'),
            504 => new TranslatableMessage('page.error.504.message'),
            505 => new TranslatableMessage('page.error.505.message'),
            506 => new TranslatableMessage('page.error.506.message'),
            507 => new TranslatableMessage('page.error.507.message'),
            508 => new TranslatableMessage('page.error.508.message'),
            510 => new TranslatableMessage('page.error.510.message'),
            511 => new TranslatableMessage('page.error.511.message'),
            default => new TranslatableMessage('page.error.default.message'),
        };
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
