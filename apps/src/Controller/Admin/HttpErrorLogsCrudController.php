<?php

namespace Labstag\Controller\Admin;

use DeviceDetector\DeviceDetector;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\HttpErrorLogs;
use Labstag\Field\HttpLogs\IsBotField;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class HttpErrorLogsCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->configureActionsTrash($actions);
        $actions->remove(Crud::PAGE_INDEX, Action::NEW);
        $actions->remove(Crud::PAGE_INDEX, Action::EDIT);

        return $actions;
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $maxLength = $pageName === Crud::PAGE_DETAIL ? 1024 : 32;
        yield $this->addFieldID();
        yield TextField::new('url', new TranslatableMessage('url'))->setMaxLength($maxLength);
        yield TextField::new('domain', new TranslatableMessage('domain'))->hideOnIndex();
        yield TextField::new('agent', new TranslatableMessage('agent'))->setMaxLength($maxLength);
        yield TextField::new('internetProtocol', new TranslatableMessage('InternetProtocol'));
        yield IsBotField::new('bot', new TranslatableMessage('bot'));
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        if (!is_null($currentEntity)) {
            $deviceDetector = new DeviceDetector($currentEntity->getAgent());
            $deviceDetector->parse();
            $data = [
                'deviceDetector' => $deviceDetector,
                'currentEntity'  => $currentEntity,
            ];
            $info = ArrayField::new('info', new TranslatableMessage('Information'));
            $info->hideOnIndex();
            $info->setValue($data);
            $info->setTemplatePath('admin/field/httperrorlogs/info.html.twig');

            yield $info;
        }

        yield TextField::new('referer', new TranslatableMessage('referer'))->setMaxLength($maxLength);
        yield IntegerField::new('httpCode', new TranslatableMessage('httpCode'));
        yield TextField::new('requestMethod', new TranslatableMessage('requestMethod'));
        yield $this->addCreatedAtField();
        yield $this->addUpdatedAtField();
        $fields = array_merge(
            $this->addFieldRefUser(),
        );
        foreach ($fields as $field) {
            yield $field;
        }

        if (!is_null($currentEntity)) {
            $data = $currentEntity->getRequestData();
            $datafield = ArrayField::new('data', new TranslatableMessage('Request DATA'));
            $datafield->hideOnIndex();
            $datafield->setTemplatePath('admin/field/httperrorlogs/request_data.html.twig');
            $datafield->setValue($data);

            yield $datafield;
        }
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->addFilterRefUser($filters);
        $this->addFilterEnable($filters);
        $filters->add('internetProtocol');
        $filters->add('httpCode');
        $filters->add('requestMethod');

        return $filters;
    }

    public static function getEntityFqcn(): string
    {
        return HttpErrorLogs::class;
    }
}
