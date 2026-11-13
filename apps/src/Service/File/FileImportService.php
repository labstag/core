<?php

declare(strict_types=1);

namespace Labstag\Service\File;

use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class FileImportService
{
    public function getimportCsvFile(string $path, string $delimiter = ','): array
    {
        $csv = new Csv();
        $csv->setDelimiter($delimiter);
        $csv->setSheetIndex(0);

        $spreadsheet = $csv->load($path);
        $worksheet   = $spreadsheet->getActiveSheet();

        return $this->generateJsonCSV($worksheet);
    }

    public function getimportXmlFile(string $path): array
    {
        $xml = simplexml_load_file($path);

        return json_decode(json_encode($xml), true);
    }

    private function generateJsonCSV(Worksheet $worksheet): array
    {
        $rows    = [];
        $headers = [];

        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $cells = [];
            foreach ($cellIterator as $cell) {
                $cells[] = $cell->getValue();
            }

            if (empty($headers)) {
                $headers = $cells;
                continue;
            }

            $rows[] = array_combine($headers, $cells);
        }

        return $rows;
    }
}
