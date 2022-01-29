<?php
/** Copyright github.com/greezlu */

declare(strict_types=1);

namespace WebServer\Filesystem;

use WebServer\Exceptions\LocalizedException;

/**
 * @package greezlu/ws-csv
 */
class CsvManager
{
    /**
     * @var AdminFileManager
     */
    private AdminFileManager $adminFileManager;

    /**
     * CsvManager constructor.
     */
    public function __construct()
    {
        $this->adminFileManager = new AdminFileManager();
    }

    /**
     * @param string $filePath
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    public function writeCsv(string $filePath, array $data): void
    {
        $fileDescriptor = $this->adminFileManager->getDescriptor($filePath);

        $headerList = $this->getHeaderList($data);

        flock($fileDescriptor, LOCK_EX);

        fputcsv($fileDescriptor, $headerList);

        foreach ($data as $row) {
            $csvRow = [];

            foreach ($headerList as $header) {
                $csvRow[] = $row[$header] ?? null;
            }

            fputcsv($fileDescriptor, $csvRow);
        }

        fflush($fileDescriptor);

        flock($fileDescriptor, LOCK_UN);
    }

    /**
     * @param string $filePath
     * @return array
     * @throws LocalizedException
     */
    public function readCSV(string $filePath): array
    {
        $fileDescriptor = $this->adminFileManager->getDescriptor($filePath, 'r');

        flock($fileDescriptor, LOCK_SH);

        $headerList = fgetcsv($fileDescriptor);

        if (!is_array($headerList)) {
            throw new LocalizedException('Unable to read file: ' . $filePath);
        }

        $data = [];

        while (($row = fgetcsv($fileDescriptor)) !== false) {
            $mappedRow = [];

            for ($i = 0; $i < count($row); $i++) {
                $mappedRow[$headerList[$i]] = $row[$i];
            }

            $data[] = $mappedRow;
        }

        flock($fileDescriptor, LOCK_UN);

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    private function getHeaderList(array $data): array
    {
        $headerList = [];

        foreach ($data as $row) {
            if (!is_array($row)) {
                continue;
            }

            foreach ($row as $column => $value) {
                if (!in_array($column, $headerList)) {
                    $headerList[] = $column;
                }
            }
        }

        return $headerList;
    }
}
