<?php

namespace Labstag\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Override;

final class WysiwygField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_MAX_LENGTH = TextField::OPTION_MAX_LENGTH;

    public const OPTION_NUM_OF_ROWS = 'numOfRows';

    public const OPTION_RENDER_AS_HTML = TextField::OPTION_RENDER_AS_HTML;

    public const OPTION_STRIP_TAGS = TextField::OPTION_STRIP_TAGS;

    /**
     * @param false|string|null $label
     */
    #[Override]
    public static function new(string $propertyName, $label = null): self
    {
        $field = (new self());
        $field->setProperty($propertyName);
        $field->setLabel($label);
        $field->setTemplateName('crud/field/text_editor');
        $field->setFormType(TextareaType::class);
        $field->setDefaultColumns('col-12');
        $field->setNumOfRows(40);
        $field->setCustomOption(self::OPTION_MAX_LENGTH, null);
        $field->setCustomOption(self::OPTION_NUM_OF_ROWS, 5);
        $field->setCustomOption(self::OPTION_RENDER_AS_HTML, false);
        $field->setCustomOption(self::OPTION_STRIP_TAGS, false);

        return $field;
    }

    public function renderAsHtml(bool $asHtml = true): self
    {
        $this->setCustomOption(self::OPTION_RENDER_AS_HTML, $asHtml);

        return $this;
    }

    /**
     * This option is ignored when using 'renderAsHtml()' to avoid
     * truncating contents in the middle of an HTML tag.
     */
    public function setMaxLength(int $length): self
    {
        if ($length < 1) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The argument of the "%s()" method must be 1 or higher (%d given).',
                    __METHOD__,
                    $length
                )
            );
        }

        $this->setCustomOption(self::OPTION_MAX_LENGTH, $length);

        return $this;
    }

    public function setNumOfRows(int $rows): self
    {
        if ($rows < 1) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The argument of the "%s()" method must be 1 or higher (%d given).',
                    __METHOD__,
                    $rows
                )
            );
        }

        $this->setCustomOption(self::OPTION_NUM_OF_ROWS, $rows);

        return $this;
    }

    public function stripTags(bool $stripTags = true): self
    {
        $this->setCustomOption(self::OPTION_STRIP_TAGS, $stripTags);

        return $this;
    }
}
