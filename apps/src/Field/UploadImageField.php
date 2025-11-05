<?php

// phpcs:ignoreFile

namespace Labstag\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatableInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;

final class UploadImageField implements FieldInterface
{
    use FieldTrait;

    /**
     * @param TranslatableInterface|string|false|null $label
     */
    public static function new(string $propertyName, $label = null): self
    {
        $deleteLabel      = new TranslatableMessage('Delete image');
        $downloadLabel    = new TranslatableMessage('Download');
        $mimeTypesMessage = new TranslatableMessage('Please upload a valid image (JPEG, PNG, GIF, WebP).');
        $maxSizeMessage   = new TranslatableMessage(
            'The file is too large. Its size should not exceed {{ limit }}.'
        );

        $uploadImageField = (new self());
        $uploadImageField->setProperty($propertyName);
        $uploadImageField->setTemplatePath('');
        $uploadImageField->setLabel($label);
        $uploadImageField->setFormType(VichImageType::class);
        $uploadImageField->setFormTypeOptions(
            [
                'required'       => false,
                'allow_delete'   => true,
                'delete_label'   => $deleteLabel->__toString(),
                'download_label' => $downloadLabel->__toString(),
                'download_uri'   => true,
                'image_uri'      => true,
                'asset_helper'   => true,
                'constraints'    => [
                    new File(
                        [
                            'maxSize'          => ini_get('upload_max_filesize'),
                            'mimeTypes'        => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                            'mimeTypesMessage' => $mimeTypesMessage->__toString(),
                            'maxSizeMessage'   => $maxSizeMessage->__toString(),
                        ]
                    ),
                ],
            ]
        );

        return $uploadImageField;
    }
}
