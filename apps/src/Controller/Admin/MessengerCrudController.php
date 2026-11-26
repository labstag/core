<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ArrayFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Messenger;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class MessengerCrudController extends CrudControllerAbstract
{
    public function configureActions(Actions $actions): Actions
    {
        return $actions->disable(Action::NEW)->disable(Action::EDIT)->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Message'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Messages'));
        $crud->setDefaultSort(
            ['dispatchedAt' => 'DESC']
        );

        return $crud;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('messageClass', new TranslatableMessage('Message Class'));
        yield ArrayField::new('transports', new TranslatableMessage('Transports'));
        yield ArrayField::new('headers', new TranslatableMessage('Headers'))->onlyOnDetail();
        yield TextField::new('status', new TranslatableMessage('Status'))->formatValue(
            function ($value): string {
                $colors = [
                    'pending'    => 'warning',
                    'processing' => 'info',
                    'success'    => 'success',
                    'failed'     => 'danger',
                ];
                $color = $colors[$value] ?? 'secondary';

                return sprintf('<span class="badge bg-%s">%s</span>', $color, $value);
            }
        )->renderAsHtml();
        yield IntegerField::new('retryCount', new TranslatableMessage('Retry Count'));
        yield DateTimeField::new('dispatchedAt', new TranslatableMessage('Dispatched At'));
        yield DateTimeField::new('startedAt', new TranslatableMessage('Started At'));
        yield DateTimeField::new('finishedAt', new TranslatableMessage('Finished At'));
        yield IntegerField::new('durationMs', new TranslatableMessage('Duration Ms'));
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $filters->add(
            ChoiceFilter::new('messageClass', new TranslatableMessage('Message Class'))
                ->setChoices($this->getRepository()->findDistinctMessageClasses())
        );
        $filters->add(
            ChoiceFilter::new('status', new TranslatableMessage('Status'))
                ->setChoices([
                    'pending'    => 'pending',
                    'processing' => 'processing',
                    'success'    => 'success',
                    'failed'     => 'failed',
                ])
        );
        $filters->add('retryCount');
        $filters->add(ArrayFilter::new('transports', new TranslatableMessage('Transports')));
        $filters->add('dispatchedAt');
        $filters->add('startedAt');
        $filters->add('finishedAt');
        $filters->add('durationMs');

        $this->crudFieldFactory->addFilterCreatedAtFor($filters, self::getEntityFqcn());

        $this->crudFieldFactory->addFilterStateFor($filters, self::getEntityFqcn());
        return $filters;
    }

    public static function getEntityFqcn(): string
    {
        return Messenger::class;
    }
}
