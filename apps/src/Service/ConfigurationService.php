<?php

namespace Labstag\Service;

use Labstag\Entity\Configuration;
use Labstag\Repository\ConfigurationRepository;

final class ConfigurationService
{

    private ?Configuration $configuration = null;

    public function __construct(
        private ConfigurationRepository $configurationRepository,
    )
    {
    }

    public function getConfiguration(): ?Configuration
    {
        if ($this->configuration instanceof Configuration) {
            return $this->configuration;
        }

        $configurations = $this->configurationRepository->findAll();

        $this->configuration = $configurations[0] ?? null;

        return $this->configuration;
    }

    public function getLocaleTmdb(): string
    {
        $config = $this->getConfiguration();

        return $config->getLanguageTmdb() ?? 'fr-FR';
    }

    public function getTacConfig(): string
    {
        $config   = $this->getConfiguration();
        $services = $config->getTacServices();
        if ('' == $services) {
            return '';
        }

        $data = [
            'privacyUrl'              => $config->getTacPrivacyUrl(),
            'bodyPosition'            => $config->getTacBodyPosition(),
            'hashtag'                 => $config->getTacHashtag(),
            'orientation'             => $config->getTacOrientation(),
            'groupServices'           => $config->isTacGroupServices(),
            'showDetailsOnClick'      => $config->isTacShowDetailsOnClick(),
            'serviceDefaultState'     => $config->getTacServiceDefaultState(),
            'showAlertSmall'          => $config->isTacShowAlertSmall(),
            'cookieslist'             => $config->isTacCookieslist(),
            'closePopup'              => $config->isTacClosePopup(),
            'showIcon'                => $config->isTacShowIcon(),
            'adblocker'               => $config->isTacAdblocker(),
            'DenyAllCta'              => $config->isTacDenyAllCta(),
            'AcceptAllCta'            => $config->isTacAcceptAllCta(),
            'highPrivacy'             => $config->isTacHighPrivacy(),
            'alwaysNeedConsent'       => $config->isTacAlwaysNeedConsent(),
            'handleBrowserDNTRequest' => $config->isTacHandleBrowserDNTRequest(),
            'removeCredit'            => $config->isTacRemoveCredit(),
            'moreInfoLink'            => $config->isTacMoreInfoLink(),
            'useExternalCss'          => $config->isTacUseExternalCss(),
            'useExternalJs'           => $config->isTacUseExternalJs(),
            'readmoreLink'            => $config->getTacReadmoreLink(),
            'mandatory'               => $config->isTacMandatory(),
            'mandatoryCta'            => $config->isTacMandatoryCta(),
            'googleConsentMode'       => $config->isTacGoogleConsentMode(),
            'partnersList'            => $config->isTacPartnersList(),
        ];

        if ('' !== $config->getTabIconSrc()) {
            $data['iconSrc'] = $config->getTabIconSrc();
        }

        if ('' !== $config->getTacCookieDomain()) {
            $data['cookieDomain'] = $config->getTacCookieDomain();
        }

        if ('' !== $config->getTacCustomCloserId()) {
            $data['customCloserId'] = $config->getTacCustomCloserId();
        }

        return json_encode($data);
    }
}
