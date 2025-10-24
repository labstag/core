<?php

namespace Labstag\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\ChoiceFilterType;
use Symfony\Component\Translation\TranslatableMessage;

class SeasonEpisodeFilter implements FilterInterface
{
    use FilterTrait;

    public function apply(
        QueryBuilder $queryBuilder,
        FilterDataDto $filterDataDto,
        ?FieldDto $fieldDto,
        EntityDto $entityDto,
    ): void
    {
        unset($fieldDto, $entityDto);
        if (null === $filterDataDto->getValue()) {
            return;
        }

        $alias = $filterDataDto->getEntityAlias();
        if (!$this->hasJoin($queryBuilder, 'season')) {
            $queryBuilder->join($alias . '.refseason', 'season');
        }

        if (!$this->hasJoin($queryBuilder, 'serie')) {
            $queryBuilder->join('season.refserie', 'serie');
        }

        $queryBuilder->andWhere('serie.title = :serie');
        $queryBuilder->setParameter('serie', $filterDataDto->getValue());
    }

    public function canSelectMultiple(bool $selectMultiple = true): self
    {
        $this->dto->setFormTypeOption('value_type_options.multiple', $selectMultiple);

        return $this;
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

    public static function new(string $propertyName, ?string $label = null): self
    {
        $fileField = (new self());
        $fileField->setFilterFqcn(self::class);
        $fileField->setProperty($propertyName);
        $fileField->setFormType(ChoiceFilterType::class);
        $fileField->setLabel($label ?? new TranslatableMessage('Serie'));

        return $fileField;
    }

    public function renderExpanded(bool $isExpanded = true): self
    {
        $this->dto->setFormTypeOption('value_type_options.expanded', $isExpanded);

        return $this;
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
    public function setTranslatableChoices(
        array $choiceGenerator,
    ): self
    {
        $this->dto->setFormTypeOption('value_type_options.choices', array_keys($choiceGenerator));
        $this->dto->setFormTypeOption('value_type_options.choice_label', fn ($value) => $choiceGenerator[$value]);

        return $this;
    }
}
