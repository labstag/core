<?php

namespace Labstag\Service;

use Labstag\FrontForm\Abstract\FrontFormLib;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Form\FormInterface;

final class FormService
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
            $code        = $form->getCode();
            $name        = $form->getName();
            $data[$name] = $code;
        }

        ksort($data);

        return $data;
    }

    public function execute(bool $save, string $code, FormInterface $form, bool $disable): bool
    {
        $frontform = $this->get($code);
        if (!$frontform instanceof FrontFormLib) {
            return false;
        }

        return $frontform->execute($save, $form, $disable);
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
