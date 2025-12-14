<?php

namespace Labstag\Twig\Extension;

use Labstag\Twig\Runtime\TimeExtensionRuntime;
use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TimeExtension extends AbstractExtension
{
    #[Override]
    public function getFunctions(): array
    {
        return [new TwigFunction('time_runtime', [TimeExtensionRuntime::class, 'runtime'])];
    }
}
