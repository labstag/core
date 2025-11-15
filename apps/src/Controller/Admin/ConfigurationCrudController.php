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
use Labstag\Entity\Configuration;
use Labstag\Field\WysiwygField;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Translation\TranslatableMessage;

class ConfigurationCrudController extends CrudControllerAbstract
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->setShowDetail(false);
        $this->actionsFactory->remove(Crud::PAGE_INDEX, Action::NEW);
        $this->actionsFactory->remove(Crud::PAGE_INDEX, Action::DELETE);
        $this->actionsFactory->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN);

        return $this->actionsFactory->show();
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());
        $fields = [
            TextField::new('titleFormat', new TranslatableMessage('Title format')),
            TextField::new('name', new TranslatableMessage('Site name')),
            EmailField::new('email', new TranslatableMessage('Email')),
            UrlField::new('url', new TranslatableMessage('Url')),
            EmailField::new('noreply', new TranslatableMessage('Email no-reply')),
            WysiwygField::new('Copyright', new TranslatableMessage('Copyright')),
            BooleanField::new('userShow', new TranslatableMessage('Show user')),
            BooleanField::new('userLink', new TranslatableMessage('Link user')),
        ];
        $this->crudFieldFactory->addFieldsToTab('principal', $fields);

        $this->crudFieldFactory->addTab('tmdb', FormField::addTab(new TranslatableMessage('Tmdb')));

        $choiceField = ChoiceField::new('languageTmdb', new TranslatableMessage('Language Tmdb'));
        $locales     = Locales::getNames();
        $languages   = [];
        foreach ($locales as $key => $value) {
            if (0 === substr_count((string) $key, '_')) {
                continue;
            }

            $locale            = str_replace('_', '-', $key);
            $languages[$value] = $locale;
        }

        $choiceField->setChoices($languages);
        $this->crudFieldFactory->addFieldsToTab('tmdb', [$choiceField]);

        $this->crudFieldFactory->addTab('security', FormField::addTab(new TranslatableMessage('Security')));

        $booleanField = BooleanField::new('disableEmptyAgent', new TranslatableMessage('Disable empty agent'));
        $this->crudFieldFactory->addFieldsToTab('security', [$booleanField]);

        $this->crudFieldFactory->addTab('sitemap', FormField::addTab(new TranslatableMessage('Sitemap')));
        $this->crudFieldFactory->addFieldsToTab(
            'sitemap',
            [
                BooleanField::new('sitemapPosts', new TranslatableMessage('Show posts')),
                BooleanField::new('sitemapStory', new TranslatableMessage('Show story')),
            ]
        );

        $this->crudFieldFactory->addTab('medias', FormField::addTab(new TranslatableMessage('Medias')));
        $this->crudFieldFactory->addFieldsToTab(
            'medias',
            [
                $this->crudFieldFactory->imageField(
                    'logo',
                    $pageName,
                    self::getEntityFqcn(),
                    (string) new TranslatableMessage('Logo')
                ),
                $this->crudFieldFactory->imageField(
                    'placeholder',
                    $pageName,
                    self::getEntityFqcn(),
                    (string) new TranslatableMessage('Placeholder')
                ),
            ]
        );

        $this->crudFieldFactory->addTab('tac', FormField::addTab(new TranslatableMessage('TAC')));
        $this->crudFieldFactory->addFieldsToTab('tac', $this->addTacFields());

        $this->crudFieldFactory->addTab('placeholders', FormField::addTab(new TranslatableMessage('Placeholders')));
        $this->crudFieldFactory->addFieldsToTab('placeholders', $this->addConfigureFieldsPlaceHolders($pageName));

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    public static function getEntityFqcn(): string
    {
        return Configuration::class;
    }

    private function addConfigureFieldsPlaceHolders(string $pageName): array
    {
        return [
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'chapterPlaceholder',
                $pageName,
                self::getEntityFqcn(),
                (string) new TranslatableMessage('Chapter')
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'editoPlaceholder',
                $pageName,
                self::getEntityFqcn(),
                (string) new TranslatableMessage('Edito')
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'episodePlaceholder',
                $pageName,
                self::getEntityFqcn(),
                (string) new TranslatableMessage('Episode')
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'memoPlaceholder',
                $pageName,
                self::getEntityFqcn(),
                (string) new TranslatableMessage('Memo')
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'moviePlaceholder',
                $pageName,
                self::getEntityFqcn(),
                (string) new TranslatableMessage('Movie')
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'pagePlaceholder',
                $pageName,
                self::getEntityFqcn(),
                (string) new TranslatableMessage('Page')
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'postPlaceholder',
                $pageName,
                self::getEntityFqcn(),
                (string) new TranslatableMessage('Post')
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'sagaPlaceholder',
                $pageName,
                self::getEntityFqcn(),
                (string) new TranslatableMessage('Saga')
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'seasonPlaceholder',
                $pageName,
                self::getEntityFqcn(),
                (string) new TranslatableMessage('Season')
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'seriePlaceholder',
                $pageName,
                self::getEntityFqcn(),
                (string) new TranslatableMessage('Serie')
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'starPlaceholder',
                $pageName,
                self::getEntityFqcn(),
                (string) new TranslatableMessage('Star')
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'storyPlaceholder',
                $pageName,
                self::getEntityFqcn(),
                (string) new TranslatableMessage('Story')
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'userPlaceholder',
                $pageName,
                self::getEntityFqcn(),
                (string) new TranslatableMessage('User')
            ),
        ];
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
