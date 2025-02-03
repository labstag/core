<?php

namespace Labstag\Field\HttpLogs;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

final class IsBotField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_RENDER_AS_SWITCH = 'renderAsSwitch';

    /**
     * @internal
     */
    public const OPTION_TOGGLE_URL = 'toggleUrl';

    /**
     * @param false|string|null $label
     */
    public static function new(string $propertyName, $label = null): self
    {
        $isbot = (new self());
        $isbot->setProperty($propertyName);
        $isbot->setLabel($label);
        $isbot->setTemplatePath('admin/field/boolean.html.twig');
        $isbot->setFormTypeOptions([
            'mapped'   => false,
            'required' => false,
        ]);
        $isbot->setFormType(CheckboxType::class);
        $isbot->addCssClass('field-boolean');
        $isbot->setCustomOption(self::OPTION_RENDER_AS_SWITCH, false);

        return $isbot;
    }
}
