<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Paragraph;
use Symfony\Component\HttpFoundation\Response;

interface ParagraphInterface
{
    public function content(string $view, Paragraph $paragraph): ?Response;

    public function generate(Paragraph $paragraph, array $data, bool $disable): void;

    public function getClass(): string;

    public function getClasses(Paragraph $paragraph): array;

    public function getFields(Paragraph $paragraph, string $pageName): mixed;

    public function getFooter(Paragraph $paragraph): mixed;

    public function getHeader(Paragraph $paragraph): mixed;

    public function getName(): string;

    public function getType(): string;

    public function supports(?object $object): bool;

    public function templates(Paragraph $paragraph, string $type): array;

    public function update(Paragraph $paragraph): void;
}
