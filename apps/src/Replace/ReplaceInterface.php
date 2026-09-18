<?php

namespace Labstag\Replace;

interface ReplaceInterface
{
    public function exec(): string;

    public function getCode(): string;

    public function getTitle(): string;
}
