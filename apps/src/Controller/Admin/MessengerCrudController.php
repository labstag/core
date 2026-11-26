<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Messenger;

class MessengerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Messenger::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('messageClass');
        yield TextField::new('transport');
        yield TextField::new('status')
            ->formatValue(function ($value) {
                $colors = [
                    'pending' => 'warning',
                    'processing' => 'info',
                    'success' => 'success',
                    'failed' => 'danger',
                ];
                $color = $colors[$value] ?? 'secondary';

                return sprintf('<span class="badge bg-%s">%s</span>', $color, $value);
            })
            ->renderAsHtml();
        yield IntegerField::new('retryCount');
        yield DateTimeField::new('dispatchedAt');
        yield DateTimeField::new('startedAt');
        yield DateTimeField::new('finishedAt');
        yield IntegerField::new('durationMs');
    }
}