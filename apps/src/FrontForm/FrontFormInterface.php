<?php

namespace Labstag\FrontForm;

interface FrontFormInterface
{
    public function getCode(): string;

    public function getForm(): string;

    public function getName(): string;
}
