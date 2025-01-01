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

    public function all()
    {
        $data = [];
        foreach ($this->forms as $form) {
            $code        = $form->getCode();
            $name        = $form->getName();
            $data[$name] = $code;
        }

        ksort($data);

        return $data;
    }

    public function execute(string $code, $form)
    {
        return $this->get($code)->execute($form);
    }

    public function get(string $code)
    {
        $form = null;
        foreach ($this->forms as $row) {
            if ($row->getCode() != $code) {
                continue;
            }

            $form = $row;

            break;
        }

        return $form;
    }
}
