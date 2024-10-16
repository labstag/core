<?php

namespace Labstag\Lib;

use Closure;
use Labstag\Entity\Paragraph;
use Labstag\Service\ParagraphService;
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

    public function setEntity($entity): void
    {
        $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $prototypes = $this->buildPrototypes($builder, $options);
        $builder->add('type');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_add' => false,
            'allow_delete' => false,
            'prototype' => true,
            'prototype_name' => '__name__',
            'type_name' => '_type',
            'options' => [],
            'types_options' => [],
            'index_property' => null,
            'sortable' => true,
            'sortable_field' => 'position',
            'placeholder' => 'Add new item',
            'data_class' => Paragraph::class,
        ]);
        $resolver->setNormalizer('options', $this->getOptionsNormalizer());
    }

    private function getOptionsNormalizer(): Closure
    {
        return function (Options $options, $value) {
            $value['block_name'] = 'entry';

            return $value;
        };
    }

    protected function buildPrototypes(FormBuilderInterface $builder, array $options): array
    {
        $prototypes = [];
        $types = $this->paragraphService->getAll($this->entity);
        foreach ($types as $key => $type) {
            $typeOptions = $options['options'];
            $typeOptions = array_replace($typeOptions, ['block_prefix' => '_paragraph']);
            $typeOptions = array_replace($typeOptions, [
                'row_attr' => ['class' => 'paragraph'],
            ]);

            $prototype = $this->buildPrototype(
                $builder,
                $options['prototype_name'],
                $type,
                $typeOptions
            );
            
            $prototypes[$key] = $prototype->getForm();
        }

        return $prototypes;
    }

    protected function buildPrototype(FormBuilderInterface $builder, string $name, FormTypeInterface|string $type, array $options): FormBuilderInterface
    {
        return $builder->create($this::class, $type, $options);
    }
}