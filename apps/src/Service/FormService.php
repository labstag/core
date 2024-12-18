<?php

namespace Labstag\Service;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class FormService
{
    public function __construct(
        #[AutowireIterator('labstag.forms')]
        private readonly iterable $forms
    )
    {
    }
}
