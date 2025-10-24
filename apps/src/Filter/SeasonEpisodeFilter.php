<?php

namespace Labstag\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use Symfony\Component\Translation\TranslatableMessage;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\ChoiceFilterType;

class SeasonEpisodeFilter implements FilterInterface
{
    use FilterTrait;
    
    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setFormType(ChoiceFilterType::class)
            ->setLabel($label ?? new TranslatableMessage('Serie'));
    }

    /**
     * @param array<mixed> $choices
     */
    public function setChoices(array $choices): self
    {
        $this->dto->setFormTypeOption('value_type_options.choices', $choices);

        return $this;
    }

    /**
     * @param array<string|TranslatableInterface> $choiceGenerator
     */
    public function setTranslatableChoices(array $choiceGenerator): self
    {
        $this->dto->setFormTypeOption('value_type_options.choices', array_keys($choiceGenerator));
        $this->dto->setFormTypeOption('value_type_options.choice_label', fn ($value) => $choiceGenerator[$value]);

        return $this;
    }

    public function renderExpanded(bool $isExpanded = true): self
    {
        $this->dto->setFormTypeOption('value_type_options.expanded', $isExpanded);

        return $this;
    }

    public function canSelectMultiple(bool $selectMultiple = true): self
    {
        $this->dto->setFormTypeOption('value_type_options.multiple', $selectMultiple);

        return $this;
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        if (null === $filterDataDto->getValue()) {
            return;
        }
        $alias = $filterDataDto->getEntityAlias();
        dump($queryBuilder->getDQLParts());
        if (!$this->hasJoin($queryBuilder, 'season')) {
            $queryBuilder->join($alias.'.refseason', 'season');
        }

        if (!$this->hasJoin($queryBuilder, 'serie')) {
            $queryBuilder->join('season.refserie', 'serie');
        }

        $queryBuilder->andWhere('serie.title = :serie');
        $queryBuilder->setParameter('serie', $filterDataDto->getValue());
    }

    public function hasJoin($queryBuilder, $text): bool
    {
        $dql = $queryBuilder->getDQLParts();
        foreach ($dql['join'] as $joins) {
            foreach ($joins as $join) {
                if ($join->getAlias() === $text) {
                    return true;
                }
            }
        }

        return false;
    }
}
