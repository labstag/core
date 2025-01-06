<?php

// phpcs:ignoreFile

namespace Labstag\Field;

use Closure;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Override;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

final class MetaParentField implements FieldInterface
{
    use FieldTrait;

    public const string OPTION_AUTOCOMPLETE = 'autocomplete';

    public const string OPTION_CRUD_CONTROLLER = 'crudControllerFqcn';

    /**
     * @internal this option is intended for internal use only
     */
    public const string OPTION_DOCTRINE_ASSOCIATION_TYPE = 'associationType';

    public const string OPTION_QUERY_BUILDER_CALLABLE = 'queryBuilderCallable';

    /**
     * @internal this option is intended for internal use only
     */
    public const string OPTION_RELATED_URL = 'relatedUrl';

    public const string OPTION_WIDGET = 'widget';

    /**
     * @internal this option is intended for internal use only
     */
    public const string PARAM_AUTOCOMPLETE_CONTEXT = 'autocompleteContext';

    public const string WIDGET_AUTOCOMPLETE = 'autocomplete';

    public const string WIDGET_NATIVE = 'native';

    public function autocomplete(): self
    {
        $this->setCustomOption(self::OPTION_AUTOCOMPLETE, true);

        return $this;
    }

    #[Override]
    public static function new(string $propertyName, $label = null): self
    {
        $field = (new self());
        $field->setProperty($propertyName);
        $field->setLabel($label);
        $field->hideOnForm();
        $field->setTemplatePath('admin/field/parent-meta.html.twig');
        $field->setFormType(EntityType::class);
        $field->addCssClass('field-association');
        $field->setFormTypeOptions(
            [
                'mapped'   => false,
                'required' => false,
            ]
        );
        $field->setDefaultColumns('col-md-7 col-xxl-6');
        $field->setCustomOption(self::OPTION_AUTOCOMPLETE, false);
        $field->setCustomOption(self::OPTION_CRUD_CONTROLLER, null);
        $field->setCustomOption(self::OPTION_WIDGET, self::WIDGET_AUTOCOMPLETE);
        $field->setCustomOption(self::OPTION_QUERY_BUILDER_CALLABLE, null);
        $field->setCustomOption(self::OPTION_RELATED_URL, null);
        $field->setCustomOption(self::OPTION_DOCTRINE_ASSOCIATION_TYPE, null);

        return $field;
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function renderAsNativeWidget(bool $asNative = true): self
    {
        $this->setCustomOption(
            self::OPTION_WIDGET,
            $asNative ? self::WIDGET_NATIVE : self::WIDGET_AUTOCOMPLETE
        );

        return $this;
    }

    public function setCrudController(string $crudControllerFqcn): self
    {
        $this->setCustomOption(self::OPTION_CRUD_CONTROLLER, $crudControllerFqcn);

        return $this;
    }

    public function setQueryBuilder(Closure $queryBuilderCallable): self
    {
        $this->setCustomOption(self::OPTION_QUERY_BUILDER_CALLABLE, $queryBuilderCallable);

        return $this;
    }
}
