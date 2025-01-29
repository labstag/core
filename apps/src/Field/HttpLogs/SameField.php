<?php

namespace Labstag\Field\HttpLogs;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

final class SameField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_NUMBER_FORMAT = 'numberFormat';

    /**
     * @param string|false|null $label
     */
    public static function new(string $propertyName, $label = null): self
    {
        $sameField = (new self());
        $sameField->setProperty($propertyName);
        $sameField->setLabel($label);
        $sameField->setTemplatePath('admin/field/integer.html.twig');
        $sameField->setFormType(IntegerType::class);
        $sameField->setFormTypeOptions(
                [
                    'mapped'   => false,
                    'required' => false,
                ]
                );
        $sameField->addCssClass('field-integer');
        $sameField->setDefaultColumns('col-md-4 col-xxl-3');
        $sameField->setCustomOption(self::OPTION_NUMBER_FORMAT, null);

        return $sameField;
    }

    // this format is passed directly to the first argument of `sprintf()` to format the integer before displaying it
    public function setNumberFormat(string $sprintfFormat): self
    {
        $this->setCustomOption(self::OPTION_NUMBER_FORMAT, $sprintfFormat);

        return $this;
    }
}
