<?php

namespace Labstag\Naming;

use Throwable;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Vich\UploaderBundle\Exception\NameGenerationException;
use Vich\UploaderBundle\Mapping\PropertyMappingInterface;
use Vich\UploaderBundle\Naming\ConfigurableInterface;
use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Naming\Polyfill\FileExtensionTrait;

final class SlugSuffixNamer implements NamerInterface, ConfigurableInterface
{
    use FileExtensionTrait;

    private bool $keepExtension = false;

    public function configure(array $options): void
    {
        $this->keepExtension = isset($options['keep_extension']) ? (bool) $options['keep_extension'] : $this->keepExtension;
    }

    public function name(object|array $object, PropertyMappingInterface $mapping): string
    {
        $file     = $mapping->getFile($object);
        $accessor = PropertyAccess::createPropertyAccessor();
        try {
            $slug = $accessor->getValue($object, 'slug');
        } catch (Throwable $throwable) {
            throw new NameGenerationException('File name could not be generated: property slug does not exist.', 0, $throwable);
        }

        if (null === $slug || '' === $slug) {
            throw new NameGenerationException('File name could not be generated: property slug is empty.');
        }

        $slug = (string) $slug;

        $fileProperty = $mapping->getFilePropertyName();
        $suffix       = str_starts_with($fileProperty, 'backdrop') ? 'backdrop' : 'poster';

        $name = $slug . '-' . $suffix;

        if ($extension = $this->getExtensionWithOption($file, $this->keepExtension)) {
            $name = sprintf('%s.%s', $name, $extension);
        }

        return $name;
    }
}
