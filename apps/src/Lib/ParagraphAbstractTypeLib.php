<?php

namespace Labstag\Lib;

use Closure;
use Labstag\Entity\Paragraph;
use Labstag\Service\ParagraphService;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class ParagraphAbstractTypeLib extends AbstractType
{

    protected $entity;

    public function __construct(
        protected ParagraphService $paragraphService
    )
    {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $prototypes = $this->buildPrototypes($formBuilder, $options);
        unset($prototypes);
        $formBuilder->add('type');
    }

    #[Override]
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'allow_add'      => false,
                'allow_delete'   => false,
                'prototype'      => true,
                'prototype_name' => '__name__',
                'type_name'      => '_type',
                'options'        => [],
                'types_options'  => [],
                'index_property' => null,
                'sortable'       => true,
                'sortable_field' => 'position',
                'placeholder'    => 'Add new item',
                'data_class'     => Paragraph::class,
            ]
        );
        $optionsResolver->setNormalizer('options', $this->getOptionsNormalizer());
    }

    public function setEntity($entity): void
    {
        $this->entity = $entity;
    }

    protected function buildPrototype(FormBuilderInterface $formBuilder, string $name, FormTypeInterface|string $type, array $options): FormBuilderInterface
    {
        return $formBuilder->create($name, $type, $options);
    }

    protected function buildPrototypes(FormBuilderInterface $formBuilder, array $options): array
    {
        $prototypes = [];
        $types      = $this->paragraphService->getAll($this->entity);
        foreach ($types as $key => $type) {
            $typeOptions = $options['options'];
            $typeOptions = array_replace($typeOptions, ['block_prefix' => '_paragraph']);
            $typeOptions = array_replace(
                $typeOptions,
                [
                    'row_attr' => ['class' => 'paragraph'],
                ]
            );

            $prototype = $this->buildPrototype(
                $formBuilder,
                $options['prototype_name'],
                $type,
                $typeOptions
            );

            $prototypes[$key] = $prototype->getForm();
        }

        return $prototypes;
    }

    private function getOptionsNormalizer(): Closure
    {
        return function (Options $options, $value)
        {
            unset($options);
            $value['block_name'] = 'entry';

            return $value;
        };
    }
}
