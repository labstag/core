<?php

namespace Labstag\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use ZipArchive;

final class GeocodeService
{
    public const HTTP_OK = 200;

    public function __construct(
        private HttpClientInterface $httpClient,
    )
    {
    }

    /**
     * @return mixed[]
     */
    public function csv(string $country): array
    {
        $country    = strtoupper($country);
        $file       = 'http://download.geonames.org/export/zip/' . $country . '.zip';
        $response   = $this->httpClient->request('GET', $file);
        $statusCode = $response->getStatusCode();
        if (self::HTTP_OK !== $statusCode) {
            return [];
        }

        $content = $response->getContent();
        /** @var resource $tempFile */
        $tempFile = tmpfile();
        $path     = stream_get_meta_data($tempFile)['uri'];
        file_put_contents($path, $content);
        $zipArchive = new ZipArchive();
        if (!$zipArchive->open($path)) {
            return [];
        }

        $content = (string) $zipArchive->getFromName($country . '.txt');
        $csv     = str_getcsv($content, "\n", escape: '\\');
        $zipArchive->close();

        return $csv;
    }

    /**
     * @param mixed[] $csv
     *
     * @return mixed[]
     */
    public function tables(array $csv): array
    {
        return array_map(fn ($line): array => str_getcsv((string) $line, "\t", escape: '\\'), $csv);
    }
}
