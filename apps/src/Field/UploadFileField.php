<?php

// phpcs:ignoreFile

namespace Labstag\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatableInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;

final class UploadFileField implements FieldInterface
{
    use FieldTrait;

    /**
     * @param TranslatableInterface|string|false|null $label
     */
    public static function new(string $propertyName, $label = null): self
    {
        $deleteLabel      = new TranslatableMessage('Delete file');
        $downloadLabel    = new TranslatableMessage('Download');
        $maxSizeMessage   = new TranslatableMessage(
            'The file is too large. Its size should not exceed {{ limit }}.'
        );

        $uploadFileField = (new self());
        $uploadFileField->setProperty($propertyName);
        $uploadFileField->setTemplatePath('');
        $uploadFileField->setLabel($label);
        $uploadFileField->setFormType(VichFileType::class);
        $uploadFileField->setFormTypeOptions(
            [
                'required'       => false,
                'allow_delete'   => true,
                'delete_label'   => $deleteLabel->__toString(),
                'download_label' => $downloadLabel->__toString(),
                'download_uri'   => true,
                'asset_helper'   => true,
                'constraints'    => [
                    new File(
                        [
                            'maxSize'          => ini_get('upload_max_filesize'),
                            'maxSizeMessage'   => $maxSizeMessage->__toString(),
                        ]
                    ),
                ],
            ]
        );

        return $uploadFileField;
    }
}
