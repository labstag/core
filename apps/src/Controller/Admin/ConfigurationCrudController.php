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
use Override;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Translation\TranslatableMessage;

class ConfigurationCrudController extends CrudControllerAbstract
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->setShowDetail(false);
        $this->actionsFactory->remove(Crud::PAGE_INDEX, Action::NEW);
        $this->actionsFactory->remove(Crud::PAGE_INDEX, Action::DELETE);
        $this->actionsFactory->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN);

        return $this->actionsFactory->show();
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());
        $copyrightTranslation = new TranslatableMessage('Copyright');
        $fields               = [
            TextField::new('titleFormat', new TranslatableMessage('Title format')),
            TextField::new('name', new TranslatableMessage('Site name')),
            EmailField::new('email', new TranslatableMessage('Email')),
            UrlField::new('url', new TranslatableMessage('Url')),
            EmailField::new('noreply', new TranslatableMessage('Email no-reply')),
            WysiwygField::new('Copyright', $copyrightTranslation->getMessage()),
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
        $textField = TextField::new('regionTmdb', new TranslatableMessage('Region'));
        $this->crudFieldFactory->addFieldsToTab('tmdb', [$choiceField, $textField]);

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

        $logoTranslation        = new TranslatableMessage('Logo');
        $placeHolderTranslation = new TranslatableMessage('Placeholder');
        $this->crudFieldFactory->addFieldsToTab(
            'medias',
            [
                $this->crudFieldFactory->imageField(
                    'logo',
                    $pageName,
                    self::getEntityFqcn(),
                    $logoTranslation->getMessage()
                ),
                $this->crudFieldFactory->imageField(
                    'placeholder',
                    $pageName,
                    self::getEntityFqcn(),
                    $placeHolderTranslation->getMessage()
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
        $chapterTranslation = new TranslatableMessage('Chapter');
        $editoTranslation   = new TranslatableMessage('Edito');
        $episodeTranslation = new TranslatableMessage('Episode');
        $memoTranslation    = new TranslatableMessage('Memo');
        $movieTranslation   = new TranslatableMessage('Movie');
        $gameTranslation    = new TranslatableMessage('Game');
        $pageTranslation    = new TranslatableMessage('Page');
        $postTranslation    = new TranslatableMessage('Post');
        $sagaTranslation    = new TranslatableMessage('Saga');
        $seasonTranslation  = new TranslatableMessage('Season');
        $serieTranslation   = new TranslatableMessage('Serie');
        $starTranslation    = new TranslatableMessage('Star');
        $storyTranslation   = new TranslatableMessage('Story');
        $userTranslation    = new TranslatableMessage('User');

        return [
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'chapterPlaceholder',
                $pageName,
                self::getEntityFqcn(),
                $chapterTranslation->getMessage()
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'editoPlaceholder',
                $pageName,
                self::getEntityFqcn(),
                $editoTranslation->getMessage()
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'episodePlaceholder',
                $pageName,
                self::getEntityFqcn(),
                $episodeTranslation->getMessage()
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'memoPlaceholder',
                $pageName,
                self::getEntityFqcn(),
                $memoTranslation->getMessage()
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'moviePlaceholder',
                $pageName,
                self::getEntityFqcn(),
                $movieTranslation->getMessage()
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'gamePlaceholder',
                $pageName,
                self::getEntityFqcn(),
                $gameTranslation->getMessage()
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'pagePlaceholder',
                $pageName,
                self::getEntityFqcn(),
                $pageTranslation->getMessage()
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'postPlaceholder',
                $pageName,
                self::getEntityFqcn(),
                $postTranslation->getMessage()
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'sagaPlaceholder',
                $pageName,
                self::getEntityFqcn(),
                $sagaTranslation->getMessage()
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'seasonPlaceholder',
                $pageName,
                self::getEntityFqcn(),
                $seasonTranslation->getMessage()
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'seriePlaceholder',
                $pageName,
                self::getEntityFqcn(),
                $serieTranslation->getMessage()
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'starPlaceholder',
                $pageName,
                self::getEntityFqcn(),
                $starTranslation->getMessage()
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'storyPlaceholder',
                $pageName,
                self::getEntityFqcn(),
                $storyTranslation->getMessage()
            ),
            FormField::addColumn(6),
            $this->crudFieldFactory->imageField(
                'userPlaceholder',
                $pageName,
                self::getEntityFqcn(),
                $userTranslation->getMessage()
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
            'tacGroupServices'           => new TranslatableMessage('Group Services'),
            'tacShowDetailsOnClick'      => new TranslatableMessage('Show Details On Click'),
            'tacShowAlertSmall'          => new TranslatableMessage('Show Alert Small'),
            'tacCookieslist'             => new TranslatableMessage('Cookies List'),
            'tacClosePopup'              => new TranslatableMessage('Close popup'),
            'tacShowIcon'                => new TranslatableMessage('Show Icon'),
            'tacAdblocker'               => new TranslatableMessage('Adblocker'),
            'tacDenyAllCta'              => new TranslatableMessage('Deny All CTA'),
            'tacAcceptAllCta'            => new TranslatableMessage('Accept All CTA'),
            'tacHighPrivacy'             => new TranslatableMessage('High Privacy'),
            'tacAlwaysNeedConsent'       => new TranslatableMessage('Always Need Consent'),
            'tacHandleBrowserDNTRequest' => new TranslatableMessage('Handle Browser DNT Request'),
            'tacRemoveCredit'            => new TranslatableMessage('Remove Credit'),
            'tacMoreInfoLink'            => new TranslatableMessage('More Info Link'),
            'tacUseExternalCss'          => new TranslatableMessage('User External CSS'),
            'tacUseExternalJs'           => new TranslatableMessage('Use External Js'),
            'tacMandatory'               => new TranslatableMessage('Mandatory'),
            'tacMandatoryCta'            => new TranslatableMessage('Mandatory CTA'),
            'tacGoogleConsentMode'       => new TranslatableMessage('Google Censent Mode'),
            'tacPartnersList'            => new TranslatableMessage('Partners List'),
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
