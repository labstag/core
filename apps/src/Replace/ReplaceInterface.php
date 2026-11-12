<?php

declare(strict_types=1);

namespace Labstag\Replace;

interface ReplaceInterface
{
    public function exec(): string;

    public function getCode(): string;

    public function getTitle(): string;
}
