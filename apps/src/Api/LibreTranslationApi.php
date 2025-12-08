<?php

namespace Labstag\Api;

use Exception;
use Labstag\Service\CacheService;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * LibreTranslate API Client
 * API documentation: https://libretranslate.com/docs.
 */
class LibreTranslationApi
{
    public const HTTP_OK = 200;

    public function __construct(
        private CacheService $cacheService,
        private HttpClientInterface $httpClient,
        private string $translationApiUrl,
        private ?string $translationApiKey = null,
    )
    {
    }

    /**
     * Détecte automatiquement la langue d'un texte.
     *
     * @param string $text Le texte à analyser
     *
     * @return array{language: string, confidence: float, success: bool, error?: string}
     */
    public function detectLanguage(string $text): array
    {
        $cacheKey = 'language_detect_' . md5($text);

        return $this->cacheService->get(
            $cacheKey,
            function () use ($text): array {
                $headers = ['Content-Type' => 'application/json'];

                if (null !== $this->translationApiKey) {
                    $headers['Authorization'] = 'Basic ' . $this->translationApiKey;
                }

                try {
                    $detectUrl = str_replace('/translate', '/detect', $this->translationApiUrl);
                    $response  = $this->httpClient->request(
                        'POST',
                        $detectUrl,
                        [
                            'headers' => $headers,
                            'json'    => ['q' => $text],
                        ]
                    );

                    $statusCode = $response->getStatusCode();
                    if (self::HTTP_OK !== $statusCode) {
                        return [
                            'language'   => '',
                            'confidence' => 0.0,
                            'success'    => false,
                            'error'      => 'HTTP Error: ' . $statusCode,
                        ];
                    }

                    $content  = $response->toArray();
                    $detected = $content[0] ?? [];

                    return [
                        'language'   => $detected['language'] ?? '',
                        'confidence' => $detected['confidence'] ?? 0.0,
                        'success'    => true,
                    ];
                } catch (TransportExceptionInterface|Exception $exception) {
                    return [
                        'language'   => '',
                        'confidence' => 0.0,
                        'success'    => false,
                        'error'      => $exception->getMessage(),
                    ];
                }
            },
            3600
            // Cache pendant 1 heure
        );
    }

    /**
     * Récupère la liste des langues disponibles.
     *
     * @return array{languages: array<array{code: string, name: string}>, success: bool, error?: string}
     */
    public function getLanguages(): array
    {
        $cacheKey = 'translation_languages';

        return $this->cacheService->get(
            $cacheKey,
            function (): array {
                $headers = [];

                if (null !== $this->translationApiKey) {
                    $headers['Authorization'] = 'Basic ' . $this->translationApiKey;
                }

                try {
                    $languagesUrl = str_replace('/translate', '/languages', $this->translationApiUrl);
                    $response     = $this->httpClient->request(
                        'GET',
                        $languagesUrl,
                        ['headers' => $headers]
                    );

                    $statusCode = $response->getStatusCode();
                    if (self::HTTP_OK !== $statusCode) {
                        return [
                            'languages' => [],
                            'success'   => false,
                            'error'     => 'HTTP Error: ' . $statusCode,
                        ];
                    }

                    $content = $response->toArray();

                    return [
                        'languages' => $content,
                        'success'   => true,
                    ];
                } catch (TransportExceptionInterface|Exception $exception) {
                    return [
                        'languages' => [],
                        'success'   => false,
                        'error'     => $exception->getMessage(),
                    ];
                }
            },
            604800
            // Cache pendant 7 jours
        );
    }

    /**
     * Traduit un texte d'une langue source vers une langue cible.
     *
     * @param string $text           Le texte à traduire
     * @param string $sourceLanguage La langue source (ex: 'fr', 'en')
     * @param string $targetLanguage La langue cible (ex: 'en', 'fr')
     *
     * @return array{translatedText: string, success: bool, error?: string}
     */
    public function translate(string $text, string $sourceLanguage, string $targetLanguage): array
    {
        $cacheKey = sprintf('translation_%s_%s_%s', md5($text), $sourceLanguage, $targetLanguage);

        return $this->cacheService->get(
            $cacheKey,
            function () use ($text, $sourceLanguage, $targetLanguage): array {
                $headers = ['Content-Type' => 'application/json'];

                if (null !== $this->translationApiKey) {
                    $headers['Authorization'] = 'Basic ' . $this->translationApiKey;
                }

                try {
                    $response = $this->httpClient->request(
                        'POST',
                        $this->translationApiUrl,
                        [
                            'headers' => $headers,
                            'json'    => [
                                'q'      => $text,
                                'source' => $sourceLanguage,
                                'target' => $targetLanguage,
                            ],
                        ]
                    );

                    $statusCode = $response->getStatusCode();
                    if (self::HTTP_OK !== $statusCode) {
                        return [
                            'translatedText' => '',
                            'success'        => false,
                            'error'          => 'HTTP Error: ' . $statusCode,
                        ];
                    }

                    $content = $response->toArray();

                    return [
                        'translatedText' => $content['translatedText'] ?? '',
                        'success'        => true,
                    ];
                } catch (TransportExceptionInterface|Exception $exception) {
                    return [
                        'translatedText' => '',
                        'success'        => false,
                        'error'          => $exception->getMessage(),
                    ];
                }
            },
            86400
            // Cache pendant 24 heures
        );
    }
}
