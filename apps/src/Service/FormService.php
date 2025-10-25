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

    /**
     * @param FormInterface<mixed> $form
     */
    public function execute(FormInterface $form, string $code, bool $disable = false, bool $save = true): bool
    {
        $frontform = $this->get($code);
        if (!$frontform instanceof FrontFormAbstract) {
            return false;
        }

        return $frontform->execute($form, $disable, $save);
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
}
