<?php

namespace Labstag\Service;

use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use Symfony\Component\HttpFoundation\RequestStack;

class JsonPaginatorService
{
    public function __construct(
        private RequestStack $requestStack,
    )
    {
    }

    /**
     * @param string $path Chemin vers le fichier JSON
     *
     * @return array ['data' => array, 'totalPages' => int, 'currentPage' => int]
     */
    public function paginate(string $path, string $field): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $assets  = Asset::fromEasyAdminAssetPackage('field-image.js');

        $page    = max(1, (int) $request->query->get('page', 1));
        $perPage = $request->query->get('offset', 20);

        if (!is_file($path)) {
            return [
                'data'        => [],
                'totalPages'  => 0,
                'currentPage' => $page,
            ];
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);

        usort($data, fn (array $a, array $b): int => strcmp((string) $a[$field], (string) $b[$field]));

        $totalItems = count($data);
        $totalPages = (int) ceil($totalItems / $perPage);

        $offset   = ($page - 1) * $perPage;
        $pageData = array_slice($data, $offset, $perPage);

        foreach ($pageData as $key => $data) {
            if (isset($data['date']['date'])) {
                $pageData[$key]['date'] = new DateTime($data['date']['date']);
            }
        }

        $params = $request->query->all();
        if (isset($params['page'])) {
            unset($params['page']);
        }

        $pageRange = [];
        $start     = max(1, $page - 4);
        $end       = min($totalPages, $page + 4);

        for ($i = $start; $i <= $end; ++$i) {
            $pageRange[$i] = $i;
        }

        $pageRange[1] = 1;
        $pageRange[$totalPages] = $totalPages;

        ksort($pageRange);

        return [
            'assets'      => [$assets->getAsDto()],
            'total'       => $totalItems,
            'currentUrl'  => http_build_query($params),
            'data'        => $pageData,
            'totalPages'  => $totalPages,
            'pageRange'   => $pageRange,
            'currentPage' => $page,
        ];
    }
}
