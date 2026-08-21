<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\BanIp;
use Labstag\Field\WysiwygField;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class BanIpCrudController extends CrudControllerAbstract
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->setReadOnly(true);

        return $this->actionsFactory->show();
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Ban IP'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Ban IP'));
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());
        $translatableMessage = new TranslatableMessage('Reason');
        $fields              = [
            $this->crudFieldFactory->booleanField('enable', new TranslatableMessage('Enable')),
            TextField::new('InternetProtocol', new TranslatableMessage('IP')),
            WysiwygField::new('reason', $translatableMessage->getMessage()),
        ];
        $this->crudFieldFactory->addFieldsToTab('principal', $fields);
        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    public static function getEntityFqcn(): string
    {
        return BanIp::class;
    }
}
