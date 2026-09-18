<?php

namespace Labstag\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\ChoiceFilterType;
use Labstag\Service\BlockService;
use Labstag\Service\ParagraphService;
use Symfony\Component\Translation\TranslatableMessage;

class DiscriminatorTypeFilter implements FilterInterface
{
    use FilterTrait;

    protected ?BlockService $blockService = null;

    protected ?ParagraphService $paragraphService = null;

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

        $service = (is_null($this->paragraphService)) ? $this->blockService : $this->paragraphService;

        if (!is_array($value)) {
            $paragraph = $service->getByCode($value);
            if (!is_null($paragraph)) {
                $class = $paragraph->getClass();
                $alias = $filterDataDto->getEntityAlias();
                $queryBuilder->resetDQLPart('from');
                $queryBuilder->from($class, $alias);
            }
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

    public function setBlockService(BlockService $blockService): self
    {
        $this->blockService = $blockService;

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

    public function setParagraphService(ParagraphService $paragraphService): self
    {
        $this->paragraphService = $paragraphService;

        return $this;
    }
}
