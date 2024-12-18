<?php

namespace Labstag\Service;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class TemplateService
{
    public function __construct(
        #[AutowireIterator('labstag.templates')]
        private readonly iterable $templates
    )
    {
    }

    public function all()
    {
        return $this->templates;
    }

    public function get(string $code, array $data = [])
    {
        $template = null;
        foreach ($this->templates as $row) {
            if ($row->getType() != $code) {
                continue;
            }

            $template = $row;
            $template->setData($data);

            break;
        }

        return $template;
    }
}
