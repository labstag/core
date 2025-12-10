<?php

namespace Labstag\Service;

use Labstag\FrontForm\FrontFormAbstract;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Form\FormInterface;

final class FormService
{
    public function __construct(
        /**
         * @var iterable<FrontFormAbstract>
         */
        #[AutowireIterator('labstag.frontforms')]
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

    public function get(string $code): ?FrontFormAbstract
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

    public function setParamsTwig(
        FormInterface $form,
        string|bool $formCode,
        $paragraph,
        $data,
        bool $disable = false,
        bool $save = true,
    ): array
    {
        $frontform = $this->get($formCode);
        if (!$frontform instanceof FrontFormAbstract) {
            return [];
        }

        return $frontform->setParamsTwig($form, $paragraph, $data, $disable, $save);
    }
}
