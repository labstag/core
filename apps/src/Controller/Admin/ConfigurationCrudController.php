<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Labstag\Entity\Configuration;
use Labstag\Field\WysiwygField;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class ConfigurationCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $actions->remove(Crud::PAGE_INDEX, Action::NEW);
        $actions->remove(Crud::PAGE_INDEX, Action::DELETE);
        $actions->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN);

        return $actions;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield $this->addTabPrincipal();
        yield TextField::new('titleFormat', new TranslatableMessage('Title format'));
        yield TextField::new('name', new TranslatableMessage('Site name'));
        yield EmailField::new('email', new TranslatableMessage('Email'));
        yield UrlField::new('url', new TranslatableMessage('Url'));
        yield EmailField::new('noreply', new TranslatableMessage('Email no-reply'));
        yield WysiwygField::new('Copyright', new TranslatableMessage('Copyright'));
        yield BooleanField::new('userShow', new TranslatableMessage('Show user'));
        yield BooleanField::new('userLink', new TranslatableMessage('Link user'));
        yield FormField::addTab(new TranslatableMessage('Security'));
        yield BooleanField::new('disableEmptyAgent', new TranslatableMessage('Disable empty agent'));
        yield FormField::addTab(new TranslatableMessage('Sitemap'));
        yield BooleanField::new('sitemapPosts', new TranslatableMessage('Show posts'));
        yield BooleanField::new('sitemapStory', new TranslatableMessage('Show story'));
        yield FormField::addTab(new TranslatableMessage('Medias'));
        yield $this->addFieldImageUpload('logo', $pageName, new TranslatableMessage('Logo'));
        yield $this->addFieldImageUpload('placeholder', $pageName, new TranslatableMessage('Placeholder'));
        yield FormField::addTab(new TranslatableMessage('TAC'));
        $fields = array_merge([], $this->addTacFields());
        foreach ($fields as $field) {
            yield $field;
        }
    }

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

        return [
            TextareaField::new('tacServices', new TranslatableMessage('Services')),
            TextField::new('tacPrivacyUrl', new TranslatableMessage('Privacy Url')),
            TextField::new('tacBodyPosition', new TranslatableMessage('Body Position')),
            TextField::new('tacHashtag', new TranslatableMessage('Hashtag')),
            TextField::new('tacCookieName', new TranslatableMessage('Cookie Name')),
            $choiceField,
            $this->addFieldBoolean('tacGroupServices', new TranslatableMessage('Group Services')),
            $this->addFieldBoolean('tacShowDetailsOnClick', new TranslatableMessage('Show Details On Click')),
            TextField::new('tacServiceDefaultState', new TranslatableMessage('Service Default State')),
            $this->addFieldBoolean('tacShowAlertSmall', new TranslatableMessage('Show Alert Small')),
            $this->addFieldBoolean('tacCookieslist', new TranslatableMessage('Cookies List')),
            $this->addFieldBoolean('tacClosePopup', new TranslatableMessage('Close popup')),
            $this->addFieldBoolean('tacShowIcon', new TranslatableMessage('Show Icon')),
            TextField::new('tabIconSrc', new TranslatableMessage('Icon src')),
            $iconPositionField,
            $this->addFieldBoolean('tacAdblocker', new TranslatableMessage('Adblocker')),
            $this->addFieldBoolean('tacDenyAllCta', new TranslatableMessage('Deny All CTA')),
            $this->addFieldBoolean('tacAcceptAllCta', new TranslatableMessage('Accept All CTA')),
            $this->addFieldBoolean('tacHighPrivacy', new TranslatableMessage('High Privacy')),
            $this->addFieldBoolean('tacAlwaysNeedConsent', new TranslatableMessage('Always Need Consent')),
            $this->addFieldBoolean('tacHandleBrowserDNTRequest', new TranslatableMessage('Handle Browser DNT Request')),
            $this->addFieldBoolean('tacRemoveCredit', new TranslatableMessage('Remove Credit')),
            $this->addFieldBoolean('tacMoreInfoLink', new TranslatableMessage('More Info Link')),
            $this->addFieldBoolean('tacUseExternalCss', new TranslatableMessage('User External CSS')),
            $this->addFieldBoolean('tacUseExternalJs', new TranslatableMessage('Use External Js')),
            TextField::new('tacCookieDomain', new TranslatableMessage('Cookie Domain')),
            TextField::new('tacReadmoreLink', new TranslatableMessage('Read more Link')),
            $this->addFieldBoolean('tacMandatory', new TranslatableMessage('Mandatory')),
            $this->addFieldBoolean('tacMandatoryCta', new TranslatableMessage('Mandatory CTA')),
            TextField::new('tacCustomCloserId', new TranslatableMessage('Custom Close ID')),
            $this->addFieldBoolean('tacGoogleConsentMode', new TranslatableMessage('Google Censent Mode')),
            $this->addFieldBoolean('tacPartnersList', new TranslatableMessage('Partners List')),
        ];
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Configuration::class;
    }
}
