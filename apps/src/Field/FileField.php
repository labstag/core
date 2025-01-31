<?php

// phpcs:ignoreFile

namespace Labstag\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use InvalidArgumentException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Contracts\Translation\TranslatableInterface;

final class FileField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_MAX_LENGTH = 'maxLength';

    public const OPTION_RENDER_AS_HTML = 'renderAsHtml';

    public const OPTION_STRIP_TAGS = 'stripTags';

    /**
     * @param null|false|string|TranslatableInterface $label
     */
    public static function new(string $propertyName, $label = null): self
    {
        $fileField = (new self());
        $fileField->onlyonDetail();
        $fileField->setProperty($propertyName);
        $fileField->setLabel($label);
        $fileField->setTemplatePath('admin/field/file.html.twig');
        $fileField->setFormType(TextType::class);
        $fileField->addCssClass('field-text');
        $fileField->setDefaultColumns('col-md-6 col-xxl-5');
        $fileField->setCustomOption(self::OPTION_MAX_LENGTH, null);
        $fileField->setCustomOption(self::OPTION_RENDER_AS_HTML, false);

        return $fileField->setCustomOption(self::OPTION_STRIP_TAGS, false);
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
            throw new InvalidArgumentException(sprintf('The argument of the "%s()" method must be 1 or higher (%d given).', __METHOD__, $length));
        }

        $this->setCustomOption(self::OPTION_MAX_LENGTH, $length);

        return $this;
    }

    public function stripTags(bool $stripTags = true): self
    {
        $this->setCustomOption(self::OPTION_STRIP_TAGS, $stripTags);

        return $this;
    }
}
