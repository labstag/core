<?php

namespace Labstag\MessageHandler;

use Labstag\Entity\GeoCode;
use Labstag\Message\GeocodeMessage;
use Labstag\Repository\GeoCodeRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GeocodeMessageHandler
{
    public function __construct(
        private GeoCodeRepository $geoCodeRepository,
    )
    {
    }

    public function __invoke(GeocodeMessage $geocodeMessage): void
    {
        $data    = $geocodeMessage->getData();
        $geocode = $this->geoCodeRepository->findOneBy(
            [
                'countryCode' => $data[0],
                'postalCode'  => $data[1],
                'placeName' => $data[2],
                'stateName' => $data[3],
                'stateCode' => $data[4],
                'provinceName' => $data[5],
                'provinceCode' => $data[6],
                'communityName' => $data[7],
                'communityCode' => $data[8],
            ]
        );
        if ($geocode instanceof GeoCode) {
            return;
        }

        $geoCode = new GeoCode();
        $geoCode->setCountryCode($data[0]);
        $geoCode->setPostalCode($data[1]);
        $geoCode->setPlaceName($data[2]);
        $geoCode->setStateName($data[3]);
        $geoCode->setStateCode($data[4]);
        $geoCode->setProvinceName($data[5]);
        $geoCode->setProvinceCode($data[6]);
        $geoCode->setCommunityName($data[7]);
        $geoCode->setCommunityCode($data[8]);
        $geoCode->setLatitude($data[9]);
        $geoCode->setLongitude($data[10]);
        $geoCode->setAccuracy((int) $data[11]);

        $this->geoCodeRepository->save($geoCode);
    }
}
