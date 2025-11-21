<?php

namespace Labstag\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\ChoiceFilterType;
use Symfony\Contracts\Translation\TranslatableInterface;

class CountriesFilter implements FilterInterface
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
        $alias      = $filterDataDto->getEntityAlias();
        $property   = $filterDataDto->getProperty();
        $comparison = $filterDataDto->getComparison();

        $parameterName = $filterDataDto->getParameterName();
        $value         = $filterDataDto->getValue();
        $search        = ('=' === $comparison) ? 'JSON_CONTAINS(%s.%s, :%s) = 1' : 'JSON_CONTAINS(%s.%s, :%s) = 0';
        $queryBuilder->andWhere(sprintf($search, $alias, $property, $parameterName));
        $queryBuilder->setParameter($parameterName, '"' . $value . '"');
    }

    /**
     * @param TranslatableInterface|string|false|null $label
     */
    public static function new(string $propertyName, $label = null): self
    {
        $countriesField = (new self());
        $countriesField->setFilterFqcn(self::class);
        $countriesField->setProperty($propertyName);
        $countriesField->setLabel($label);
        $countriesField->setFormType(ChoiceFilterType::class);
        $countriesField->setFormTypeOption('translation_domain', 'EasyAdminBundle');

        return $countriesField;
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
    public function setTranslatableChoices(array $choiceGenerator): self
    {
        $this->dto->setFormTypeOption('value_type_options.choices', array_keys($choiceGenerator));
        $this->dto->setFormTypeOption('value_type_options.choice_label', fn ($value) => $choiceGenerator[$value]);

        return $this;
    }
}
