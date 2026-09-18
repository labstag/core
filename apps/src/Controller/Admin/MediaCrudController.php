<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Media;
use Labstag\Field\UploadFileField;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class MediaCrudController extends CrudControllerAbstract
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);

        return $this->actionsFactory->show();
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Media'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Media'));
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());
        $integerField = IntegerField::new('size', new TranslatableMessage('Size'));
        $integerField->formatValue(
            function ($value, Media $media): string {
                unset($value);

                return $this->fileService->getSizeFormat($media->getSize());
            }
        );
        $integerField->hideOnForm();

        $translatableMessage = new TranslatableMessage('File');
        $uploadFileField     = UploadFileField::new('file', $translatableMessage->getMessage());
        $uploadFileField->setTranslator($this->translator);
        $uploadFileField->onlyOnForms();

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->slugField(target: 'name'),
                TextField::new('name', new TranslatableMessage('Name')),
                TextField::new('mimeType', new TranslatableMessage('Mime type'))->hideOnForm(),
                $integerField,
                $uploadFileField,
            ]
        );
        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    public static function getEntityFqcn(): string
    {
        return Media::class;
    }
}
