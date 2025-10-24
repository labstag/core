<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Labstag\Controller\Admin\Abstract\AbstractCrudControllerLib;
use Labstag\Entity\Configuration;
use Labstag\Field\WysiwygField;
use Symfony\Component\Translation\TranslatableMessage;

class ConfigurationCrudController extends AbstractCrudControllerLib
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $actions->remove(Crud::PAGE_INDEX, Action::NEW);
        $actions->remove(Crud::PAGE_INDEX, Action::DELETE);
        $actions->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN);

        return $actions;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield $this->addTabPrincipal();
        yield from [
            TextField::new('titleFormat', new TranslatableMessage('Title format')),
            TextField::new('name', new TranslatableMessage('Site name')),
            EmailField::new('email', new TranslatableMessage('Email')),
            UrlField::new('url', new TranslatableMessage('Url')),
            EmailField::new('noreply', new TranslatableMessage('Email no-reply')),
            WysiwygField::new('Copyright', new TranslatableMessage('Copyright')),
            BooleanField::new('userShow', new TranslatableMessage('Show user')),
            BooleanField::new('userLink', new TranslatableMessage('Link user')),
        ];
        yield FormField::addTab(new TranslatableMessage('Security'));
        yield BooleanField::new('disableEmptyAgent', new TranslatableMessage('Disable empty agent'));
        yield FormField::addTab(new TranslatableMessage('Sitemap'));
        yield BooleanField::new('sitemapPosts', new TranslatableMessage('Show posts'));
        yield BooleanField::new('sitemapStory', new TranslatableMessage('Show story'));
        yield FormField::addTab(new TranslatableMessage('Medias'));
        yield $this->crudFieldFactory->imageField(
            'logo',
            $pageName,
            self::getEntityFqcn(),
            (string) new TranslatableMessage('Logo')
        );
        yield $this->crudFieldFactory->imageField(
            'placeholder',
            $pageName,
            self::getEntityFqcn(),
            (string) new TranslatableMessage('Placeholder')
        );
        yield FormField::addTab(new TranslatableMessage('TAC'));
        $fields = array_merge([], $this->addTacFields());
        foreach ($fields as $field) {
            yield $field;
        }
    }

    public static function getEntityFqcn(): string
    {
        return Configuration::class;
    }

    /**
     * @return FieldInterface[]
     */
    private function addTacFields(): array
    {
        $orientations = [
            'top'    => 'top',
            'middle' => 'middle',
            'bottom' => 'bottom',
            'popup'  => 'popup',
            'banner' => 'banner',
        ];

        $choiceField = ChoiceField::new('tacOrientation', new TranslatableMessage('Orientation'));
        $choiceField->setChoices($orientations);

        $iconPosition = [
            'BottomRight' => 'BottomRight',
            'BottomLeft'  => 'BottomLeft',
            'TopRight'    => 'TopRight',
            'TopLeft'     => 'TopLeft',
        ];

        $iconPositionField = ChoiceField::new('tacIconPosition', new TranslatableMessage('icon Position'));
        $iconPositionField->setChoices($iconPosition);

        $booleanLabels = [
            'tacGroupServices'           => (string) new TranslatableMessage('Group Services'),
            'tacShowDetailsOnClick'      => (string) new TranslatableMessage('Show Details On Click'),
            'tacShowAlertSmall'          => (string) new TranslatableMessage('Show Alert Small'),
            'tacCookieslist'             => (string) new TranslatableMessage('Cookies List'),
            'tacClosePopup'              => (string) new TranslatableMessage('Close popup'),
            'tacShowIcon'                => (string) new TranslatableMessage('Show Icon'),
            'tacAdblocker'               => (string) new TranslatableMessage('Adblocker'),
            'tacDenyAllCta'              => (string) new TranslatableMessage('Deny All CTA'),
            'tacAcceptAllCta'            => (string) new TranslatableMessage('Accept All CTA'),
            'tacHighPrivacy'             => (string) new TranslatableMessage('High Privacy'),
            'tacAlwaysNeedConsent'       => (string) new TranslatableMessage('Always Need Consent'),
            'tacHandleBrowserDNTRequest' => (string) new TranslatableMessage('Handle Browser DNT Request'),
            'tacRemoveCredit'            => (string) new TranslatableMessage('Remove Credit'),
            'tacMoreInfoLink'            => (string) new TranslatableMessage('More Info Link'),
            'tacUseExternalCss'          => (string) new TranslatableMessage('User External CSS'),
            'tacUseExternalJs'           => (string) new TranslatableMessage('Use External Js'),
            'tacMandatory'               => (string) new TranslatableMessage('Mandatory'),
            'tacMandatoryCta'            => (string) new TranslatableMessage('Mandatory CTA'),
            'tacGoogleConsentMode'       => (string) new TranslatableMessage('Google Censent Mode'),
            'tacPartnersList'            => (string) new TranslatableMessage('Partners List'),
        ];

        return [
            TextareaField::new('tacServices', new TranslatableMessage('Services')),
            TextField::new('tacPrivacyUrl', new TranslatableMessage('Privacy Url')),
            TextField::new('tacBodyPosition', new TranslatableMessage('Body Position')),
            TextField::new('tacHashtag', new TranslatableMessage('Hashtag')),
            TextField::new('tacCookieName', new TranslatableMessage('Cookie Name')),
            $choiceField,
            TextField::new('tacServiceDefaultState', new TranslatableMessage('Service Default State')),
            TextField::new('tabIconSrc', new TranslatableMessage('Icon src')),
            $iconPositionField,
            TextField::new('tacCookieDomain', new TranslatableMessage('Cookie Domain')),
            TextField::new('tacReadmoreLink', new TranslatableMessage('Read more Link')),
            TextField::new('tacCustomCloserId', new TranslatableMessage('Custom Close ID')),
            // Append grouped boolean fields
            ...$this->crudFieldFactory->tacBooleanSet($booleanLabels),
        ];
    }
}
