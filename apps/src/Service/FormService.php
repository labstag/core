<?php

namespace Labstag\Service;

use Labstag\Lib\FrontFormLib;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Form\FormInterface;

class FormService
{
    public function __construct(
        #[AutowireIterator('labstag.forms')]
        private readonly iterable $forms,
    )
    {
    }

    /**
     * @return mixed[]
     */
    public function all(): array
    {
        $data = [];
        foreach ($this->forms as $form) {
            $code = $form->getCode();
            $name = $form->getName();
            $data[$name] = $code;
        }

        ksort($data);

        return $data;
    }

    public function execute(string $code, FormInterface $form, bool $disable): bool
    {
        return $this->get($code)->execute($form, $disable);
    }

    public function get(string $code): ?FrontFormLib
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
