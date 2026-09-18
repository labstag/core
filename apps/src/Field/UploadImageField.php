<?php

// phpcs:ignoreFile

namespace Labstag\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;

final class UploadImageField implements FieldInterface
{
    use FieldTrait;

    /**
     * @param TranslatableInterface|string|false|null $label
     */
    public static function new(string $propertyName, $label = null): self
    {
        $uploadImageField = (new self());
        $uploadImageField->setProperty($propertyName);
        $uploadImageField->setTemplatePath('');
        $uploadImageField->setLabel($label);
        $uploadImageField->setFormType(VichImageType::class);

        return $uploadImageField;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $deleteLabel      = new TranslatableMessage('Delete image');
        $downloadLabel    = new TranslatableMessage('Download');
        $mimeTypesMessage = new TranslatableMessage('Please upload a valid image (JPEG, PNG, GIF, WebP).');
        $maxSizeMessage   = new TranslatableMessage(
            'The file is too large. Its size should not exceed {{ limit }}.'
        );
        $this->setFormTypeOptions(
            [
                'required'       => false,
                'allow_delete'   => true,
                'delete_label'   => $translator->trans($deleteLabel->getMessage(), $deleteLabel->getParameters()),
                'download_label' => $translator->trans($downloadLabel->getMessage(), $downloadLabel->getParameters()),
                'download_uri'   => true,
                'image_uri'      => true,
                'asset_helper'   => true,
                'constraints'    => [
                    new File(
                        maxSize: ini_get('upload_max_filesize'),
                        mimeTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                        maxSizeMessage: $translator->trans(
                            $maxSizeMessage->getMessage(),
                            $maxSizeMessage->getParameters()
                        ),
                        mimeTypesMessage: $translator->trans(
                            $mimeTypesMessage->getMessage(),
                            $mimeTypesMessage->getParameters()
                        ),
                    ),
                ],
            ]
        );
    }
}
