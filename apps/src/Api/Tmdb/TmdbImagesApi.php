<?php

namespace Labstag\Api\Tmdb;

use Symfony\Contracts\Cache\ItemInterface;

/**
 * TMDB Image API Client
 * Handles all image-related functionality.
 */
class TmdbImagesApi extends AbstractTmdbApi
{
    /**
     * Get backdrop URL with optimal size.
     *
     * @param string $backdropPath The backdrop path from TMDB API
     * @param int    $targetWidth  Desired width (default: 780px)
     *
     * @return string|null Complete backdrop URL
     */
    public function getBackdropUrl(string $backdropPath, int $targetWidth = 780): ?string
    {
        return $this->getOptimizedUrl($backdropPath, 'backdrop', $targetWidth);
    }

    /**
     * Get optimized image URL based on image type and desired width.
     *
     * @param string $imagePath   The image path from TMDB API
     * @param string $imageType   Type of image: 'backdrop', 'poster', 'profile', 'logo', 'still'
     * @param int    $targetWidth Desired width in pixels
     *
     * @return string|null Optimized image URL
     */
    public function getOptimizedUrl(string $imagePath, string $imageType, int $targetWidth = 500): ?string
    {
        if ('' === trim($imagePath)) {
            return null;
        }

        $sizes     = $this->getAvailableSizes();
        $typeSizes = $sizes[$imageType . '_sizes'] ?? [];

        if (empty($typeSizes)) {
            return $this->getUrl($imagePath, 'w500');
        }

        $bestSize = $this->getBestSize($typeSizes, $targetWidth);

        return $this->getUrl($imagePath, $bestSize);
    }

    /**
     * Get poster URL with optimal size.
     *
     * @param string $posterPath  The poster path from TMDB API
     * @param int    $targetWidth Desired width (default: 342px)
     *
     * @return string|null Complete poster URL
     */
    public function getPosterUrl(string $posterPath, int $targetWidth = 342): ?string
    {
        return $this->getOptimizedUrl($posterPath, 'poster', $targetWidth);
    }

    /**
     * Get episode still image URL with optimal size.
     *
     * @param string $stillPath   The still path from TMDB API (episode image)
     * @param int    $targetWidth Desired width (default: 300px)
     *
     * @return string|null Complete still image URL
     */
    public function getStillUrl(string $stillPath, int $targetWidth = 300): ?string
    {
        return $this->getOptimizedUrl($stillPath, 'still', $targetWidth);
    }

    /**
     * Build complete image URL from TMDB path.
     *
     * @param string $imagePath The image path from TMDB API (e.g., "/kqjL17yufvn9OVLyXYpvtyrFfak.jpg")
     * @param string $size      The image size (e.g., "w500", "original")
     *
     * @return string|null Complete image URL or null if configuration fails
     */
    public function getUrl(string $imagePath, string $size = 'w500'): ?string
    {
        if ('' === trim($imagePath)) {
            return null;
        }

        $config = $this->getConfiguration();
        if (null === $config || !isset($config['images']['secure_base_url'])) {
            // Fallback to known TMDB base URL if config fails
            return 'https://image.tmdb.org/t/p/' . $size . $imagePath;
        }

        return $config['images']['secure_base_url'] . $size . $imagePath;
    }

    /**
     * Get available image sizes for different image types.
     *
     * @return array<string, array<string>> Available sizes by image type
     */
    private function getAvailableSizes(): array
    {
        $config = $this->getConfiguration();
        if (null === $config || !isset($config['images'])) {
            // Fallback to common sizes
            return [
                'backdrop_sizes' => [
                    'w300',
                    'w780',
                    'w1280',
                    'original',
                ],
                'logo_sizes'     => [
                    'w45',
                    'w92',
                    'w154',
                    'w185',
                    'w300',
                    'w500',
                    'original',
                ],
                'poster_sizes'   => [
                    'w92',
                    'w154',
                    'w185',
                    'w342',
                    'w500',
                    'w780',
                    'original',
                ],
                'profile_sizes'  => [
                    'w45',
                    'w185',
                    'h632',
                    'original',
                ],
                'still_sizes'    => [
                    'w92',
                    'w185',
                    'w300',
                    'original',
                ],
            ];
        }

        return [
            'backdrop_sizes' => $config['images']['backdrop_sizes'] ?? [],
            'logo_sizes'     => $config['images']['logo_sizes'] ?? [],
            'poster_sizes'   => $config['images']['poster_sizes'] ?? [],
            'profile_sizes'  => $config['images']['profile_sizes'] ?? [],
            'still_sizes'    => $config['images']['still_sizes'] ?? [],
        ];
    }

    private function getBestSize(array $typeSizes, int $targetWidth)
    {
        $bestSize = 'original';
        $bestDiff = PHP_INT_MAX;

        foreach ($typeSizes as $typeSize) {
            if ('original' === $typeSize) {
                continue;
                // Skip original for now, use as fallback
            }

            // Extract width from size string (e.g., 'w500' -> 500)
            if (str_starts_with((string) $typeSize, 'w')) {
                $width = (int) substr((string) $typeSize, 1);
                $diff  = abs($width - $targetWidth);

                if ($diff < $bestDiff || ($width >= $targetWidth && PHP_INT_MAX === $bestDiff)) {
                    $bestSize = $typeSize;
                    $bestDiff = $diff;
                }
            }
        }

        // If no suitable size found, use original
        if (PHP_INT_MAX === $bestDiff && in_array('original', $typeSizes, true)) {
            return 'original';
        }

        return $bestSize;
    }

    /**
     * Get API configuration details.
     *
     * @return array<string, mixed>|null
     */
    private function getConfiguration(): ?array
    {
        $cacheKey = 'tmdb_configuration';

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item): ?array {
                $url  = self::BASE_URL . '/configuration';
                $data = $this->makeRequest($url);

                if (null === $data) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache

                return $data;
            },
            604800
        );
    }
}
