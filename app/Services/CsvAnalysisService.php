<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class CsvAnalysisService{

    public function analyze(string $path): array
    {
        $storage = Storage::disk('local');
        if (!$storage->exists($path)) {
            throw new \RuntimeException('Uploaded csv file not found');
        }

        $absolutePath = $storage->path($path);
        $file = fopen($absolutePath, 'r');

        if ($file === false) {
            throw new \RuntimeException('Unable to open csv file');
        }

        try {
            $headers = fgetcsv($file);
            if ($headers === false || $headers === [null]) {
                throw new \RuntimeException('Csv file is empty');
            }

            $headers_length = count($headers);
            $rows = [];
            $structureErrors = [];
            while (count($rows) < 100 && ($row = fgetcsv($file)) !== false) {
                $rowNumber = count($rows) + 2;

                $row_length = count($row);
                if ($headers_length !== $row_length) {
                    $structureErrors[] =
                    [
                        "row" => $rowNumber,
                        "expected_columns" => $headers_length,
                        "actual_columns" => $row_length,
                    ];
                }

                $rows[] = $row;
            }

            $suggested_types = $this->suggestFields($headers, $rows);
//            $suggested_types = $suggested_types['type'];
        }finally{
            fclose($file);
        }

        return [
            'headers' => $headers,
            'sampled_rows' => count($rows),
            'rows' => $rows,
            'structure_errors' => $structureErrors,
            'suggested_types' => $suggested_types,
        ];
    }

    private function detectValueType(string $value): string
    {
        $value = trim($value);
        $int_value = filter_var($value, FILTER_VALIDATE_INT);
        if ($int_value !== false) {
            return 'integer';
        }

        if (is_numeric($value)) {
            return 'decimal';
        }

        if (in_array(strtolower($value), ['true', 'false'], true)) {
            return 'boolean';
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if ($date !== false && $date->format('Y-m-d') === $value) {
            return 'date';
        }

        return 'string';
    }

    private function inferColumnType(array $values): string
    {
        $column_types = [];
        foreach ($values as $value) {
            if (trim($value) !== '') {
                $column_types[] = $this->detectValueType($value);
            }
        }

        if ($column_types === []) {
            return 'string';
        }

        $unique_types = array_unique($column_types);
        if (count($unique_types) === 1){
            return $unique_types[0];
        }

        if (
            array_diff($unique_types, [ 'integer', 'decimal']) === []
        ) {
            return 'decimal';
        }

        return 'string';
    }

    private function suggestFields(array $headers, array $rows): array
    {
        $suggested = [];
        foreach ($headers as $index => $header) {
            $column_values = [];
            foreach ($rows as $row) {
                $column_values[] = $row[$index] ?? '';
            }

            $suggested[] = [
                'name' => $header,
                'type' => $this->inferColumnType($column_values),
            ];

        }
        return $suggested;
    }
}
