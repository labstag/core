<?php

// phpcs:ignoreFile

namespace Labstag\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use InvalidArgumentException;
use Labstag\Form\Type\ParagraphType;
use Override;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Uid\Ulid;

final class ParagraphsField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_COLLAPSED = 'collapsed';

    public const OPTION_COLLAPSIBLE = 'collapsible';

    public const OPTION_ICON = 'icon';

    public const OPTION_ROW_BREAKPOINT = 'rowBreakPoint';

    public function collapsible(bool $collapsible = true): self
    {
        if (!$this->hasLabelOrIcon()) {
            $message = sprintf(
                'The %s() method used in one of your panels requires that the panel defines either a label or an icon, but it defines none of them.',
                __METHOD__
            );

            throw new InvalidArgumentException($message);
        }

        $this->setCustomOption(self::OPTION_COLLAPSIBLE, $collapsible);

        return $this;
    }

    /**
     * @param string|TranslatableMessage|false|null $label
     */
    #[Override]
    public static function new($label = false, ?string $icon = null): self
    {
        $field = new self();
        $field->setFieldFqcn(self::class);
        $field->setProperty('ea_form_panel_' . (new Ulid()));
        $field->setLabel($label);
        $field->setFormType(ParagraphType::class);
        $field->setFormTypeOptions([
            'mapped'   => false,
            'required' => false,
        ]);
        $field->setTemplatePath('admin/field/paragraphs.html.twig');
        $field->setCustomOption(self::OPTION_ICON, $icon);
        $field->setCustomOption(self::OPTION_COLLAPSIBLE, false);
        $field->setCustomOption(self::OPTION_COLLAPSED, false);

        return $field;
    }

    public function renderCollapsed(bool $collapsed = true): self
    {
        if (!$this->hasLabelOrIcon()) {
            $message = sprintf(
                'The %s() method used in one of your panels requires that the panel defines either a label or an icon, but it defines none of them.',
                __METHOD__
            );

            throw new InvalidArgumentException($message);
        }

        $this->setCustomOption(self::OPTION_COLLAPSIBLE, true);
        $this->setCustomOption(self::OPTION_COLLAPSED, $collapsed);

        return $this;
    }

    private function hasLabelOrIcon(): bool
    {
        // don't use empty() because the label can contain only white spaces (it's a valid edge-case)
        if (!is_null($this->dto->getLabel()) && '' !== $this->dto->getLabel()) {
            return true;
        }

        return !is_null($this->dto->getCustomOption(self::OPTION_ICON));
    }
}
