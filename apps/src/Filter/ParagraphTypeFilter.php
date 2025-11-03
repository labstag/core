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

class ParagraphTypeFilter implements FilterInterface
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
        $value = $filterDataDto->getValue();
        if (null === $value || '' === $value) {
            return;
        }

        $alias = $filterDataDto->getEntityAlias();

        if (is_array($value)) {
            // TYPE(alias) IN (:types)
            $queryBuilder->andWhere(sprintf('TYPE(%s) IN (:types)', $alias));
            $queryBuilder->setParameter('types', $value);
        } else {
            // TYPE(alias) = :type
            $queryBuilder->andWhere(sprintf('TYPE(%s) = :type', $alias));
            $queryBuilder->setParameter('type', $value);
        }
    }

    public function canSelectMultiple(bool $selectMultiple = true): self
    {
        $this->dto->setFormTypeOption('value_type_options.multiple', $selectMultiple);

        return $this;
    }

    public static function new(string $propertyName, ?string $label = null): self
    {
        $filter = new self();
        $filter->setFilterFqcn(self::class);
        $filter->setProperty($propertyName);
        $filter->setFormType(ChoiceFilterType::class);
        $filter->setLabel($label ?? new TranslatableMessage('Type'));

        return $filter;
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
}
