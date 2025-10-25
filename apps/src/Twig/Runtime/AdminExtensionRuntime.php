<?php

namespace Labstag\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class AdminExtensionRuntime implements RuntimeExtensionInterface
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
        if ($years !== 0) {
            $parts[] = $years . 'a';
        }

        if ($months !== 0) {
            $parts[] = $months . 'm';
        }

        if ($days !== 0) {
            $parts[] = $days . 'j';
        }

        if ($hours !== 0) {
            $parts[] = $hours . 'h';
        }

        if ($mins !== 0) {
            $parts[] = $mins . 'min';
        }

        return implode(' ', $parts);
    }
}
