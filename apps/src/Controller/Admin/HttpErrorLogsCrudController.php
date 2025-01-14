<?php

namespace Labstag\Controller\Admin;

use DeviceDetector\DeviceDetector;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\HttpErrorLogs;
use Labstag\Field\HttpLogs\IsBotField;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

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

    public static function getEntityFqcn(): string
    {
        return HttpErrorLogs::class;
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->addFilterEnable($filters);
        $filters->add('ip');
        $filters->add('http_code');
        $filters->add('request_method');

        return $filters;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $maxLength = $pageName === Crud::PAGE_DETAIL ? 1024 : 32;
        yield $this->addFieldID();
        yield DateField::new('created');
        yield TextField::new('url')->setMaxLength($maxLength);
        yield TextField::new('domain');
        yield TextField::new('agent')->setMaxLength($maxLength);
        yield TextField::new('ip');
        yield IsBotField::new('bot');
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        if (!is_null($currentEntity)) {
            $deviceDetector = new DeviceDetector($currentEntity->getAgent());
            $deviceDetector->parse();
            $data = [
                'deviceDetector' => $deviceDetector,
                'currentEntity'  => $currentEntity,
            ];
            $info = ArrayField::new('info', 'Information');
            $info->hideOnIndex();
            $info->setValue($data);
            $info->setTemplatePath('admin/field/httperrorlogs/info.html.twig');

            yield $info;
        }

        yield TextField::new('referer')->setMaxLength($maxLength);
        yield IntegerField::new('http_code');
        yield TextField::new('request_method');
        yield AssociationField::new('refUser', 'Utilisateur');
        if (!is_null($currentEntity)) {
            $data = $currentEntity->getRequestData();
            $datafield = ArrayField::new('data', 'Request DATA');
            $datafield->hideOnIndex();
            $datafield->setTemplatePath('admin/field/httperrorlogs/request_data.html.twig');
            $datafield->setValue($data);

            yield $datafield;
        }
    }
}
