<?php

namespace Labstag\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class TimeExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct()
    {
        // Inject dependencies if needed
    }

    public function runtime($minutes): string
    {
        $minutes = (int) $minutes;
        $years   = intdiv($minutes, 525600);
        // 365*24*60
        $minutes -= $years * 525600;
        $months = intdiv($minutes, 43200);
        // 30*24*60
        $minutes -= $months * 43200;
        $days = intdiv($minutes, 1440);
        // 24*60
        $minutes -= $days * 1440;
        $hours = intdiv($minutes, 60);
        $mins  = $minutes % 60;

        $parts = [];
        if (0 !== $years) {
            $parts[] = $years . 'a';
        }

        if (0 !== $months) {
            $parts[] = $months . 'm';
        }

        if (0 !== $days) {
            $parts[] = $days . 'j';
        }

        if (0 !== $hours) {
            $parts[] = $hours . 'h';
        }

        if (0 !== $mins) {
            $parts[] = $mins . 'min';
        }

        return implode(' ', $parts);
    }
}
