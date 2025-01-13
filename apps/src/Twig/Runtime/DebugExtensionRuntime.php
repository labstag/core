<?php

namespace Labstag\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class DebugExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct()
    {
        // Inject dependencies if needed
    }

    public function begin(array $data): string
    {
        $templates = $data['templates'];
        if (is_null($templates) || 0 == (is_countable($templates) ? count($templates) : 0)) {
            return '';
        }

        $html = "<!--\nTHEME DEBUG\n";
        $html .= "THEME HOOK : '" . $templates['hook'] . "'\n";
        if (0 != (is_countable($templates['files']) ? count($templates['files']) : 0)) {
            $html .= "FILE NAME SUGGESTIONS: \n";
            foreach ($templates['files'] as $file) {
                $checked = ($templates['view'] == $file) ? 'X' : '*';
                $html .= ' ' . $checked . ' ' . $file . "\n";
            }
        }

        return $html . ("BEGIN OUTPUT from '" . $templates['view'] . "' -->\n");
    }

    public function end(array $data): string
    {
        $templates = $data['templates'];
        if (is_null($templates) || 0 == (is_countable($templates) ? count($templates) : 0)) {
            return '';
        }

        return "\n<!-- END OUTPUT from '" . $templates['view'] . "' -->\n";
    }
}
