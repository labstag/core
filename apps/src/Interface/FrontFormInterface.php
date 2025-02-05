<?php

namespace Labstag\Interface;

interface FrontFormInterface
{
    public function getCode(): string;

    public function getForm(): string;

    public function getName(): string;
}
