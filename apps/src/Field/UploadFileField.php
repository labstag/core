<?php

// phpcs:ignoreFile

namespace Labstag\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;

final class UploadFileField implements FieldInterface
{
    use FieldTrait;

    /**
     * @param TranslatableInterface|string|false|null $label
     */
    public static function new(string $propertyName, $label = null): self
    {
        $uploadFileField = (new self());
        $uploadFileField->setProperty($propertyName);
        $uploadFileField->setTemplatePath('');
        $uploadFileField->setLabel($label);
        $uploadFileField->setFormType(VichFileType::class);

        return $uploadFileField;
    }

    public function setTranslator(TranslatorInterface $translator)
    {
        $deleteLabel      = new TranslatableMessage('Delete file');
        $downloadLabel    = new TranslatableMessage('Download');
        $maxSizeMessage   = new TranslatableMessage(
            'The file is too large. Its size should not exceed {{ limit }}.'
        );
        $this->setFormTypeOptions(
            [
                'required'       => false,
                'allow_delete'   => true,
                'delete_label'   => $translator->trans($deleteLabel),
                'download_label' => $translator->trans($downloadLabel),
                'download_uri'   => true,
                'asset_helper'   => true,
                'constraints'    => [
                    new File(
                        [
                            'maxSize'          => ini_get('upload_max_filesize'),
                            'maxSizeMessage'   => $translator->trans($maxSizeMessage),
                        ]
                    ),
                ],
            ]
        );
    }
}
