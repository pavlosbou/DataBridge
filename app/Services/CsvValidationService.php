<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class CsvValidationService{

    public function validateValue(string $value, string $type): bool
    {
        $supported_types = [
            'string',
            'integer',
            'decimal',
            'boolean',
            'date'
        ];

        if (!in_array($type, $supported_types)) {
            return false;
        }

        $value = trim($value);
        if ($value === '') {
            return true;
        }

        $is_date = function (string $value):bool {
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
             return $date !== false && $date->format('Y-m-d') === $value;

        };

        return match ($type) {
            'string' => true,
            'integer' => filter_var($value, FILTER_VALIDATE_INT) !== false,
            'decimal' => is_numeric($value),
            'boolean' => in_array(strtolower($value), ['true', 'false'],true),
            'date' => $is_date($value),
            default => false
        };

    }

    public function validateRow(array $row,array $fields, int $rowNumber): array
    {
        $validation_errors = [];
        foreach ($fields as $index => $field) {
            $value = $row[$index] ?? '';
            $validation = $this->validateValue($value, $field['type']);
            if (!$validation) {
                $validation_errors[] = [
                    'row' => $rowNumber,
                    'field' => $field['name'],
                    'value' => $value,
                    'message' => "Expected {$field['type']}"
                ];
            }
        }
        return $validation_errors;
    }

    public function validateFile(string $path, array $fields): array
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
            $duplicate_headers = array_values(array_diff_assoc($headers, array_unique($headers)));

            $field_types = [];
            foreach ($fields as $field) {
                $field_types[$field['name']] = $field['type'];
            }

            $new_fields = [];
            $unknown_headers = [];

            $missing_headers = array_diff(array_keys($field_types), $headers);
            $missing_headers = array_values($missing_headers);

            foreach ($headers as $header) {
                if (!array_key_exists($header, $field_types)) {
                    $unknown_headers[] = $header;
                }else{
                    $new_fields[] = [
                        'name' => $header,
                        'type' => $field_types[$header],
                    ];
                }
            }

            if (!empty($missing_headers) || !empty($unknown_headers) || !empty($duplicate_headers)) {
                return [
                    'headers' => $headers,
                    'totalRows' => null,
                    'validationErrors' => [],
                    'invalidRows' => null,
                    'validRows' => null,
                    'missingHeaders' => $missing_headers,
                    'unknownHeaders' => $unknown_headers,
                    'duplicateHeaders' => $duplicate_headers,
                    'structureErrors' => true
                ];
            }

            $totalRows = 0;
            $countInvalidRows = 0;
            $validationErrors = [];
            while (($row = fgetcsv($file)) !== false) {
                $rowErrors = $this->validateRow($row, $new_fields, $totalRows + 2);


                if (!empty($rowErrors)) {
                    $countInvalidRows++;
                    foreach ($rowErrors as $rowError) {
                        $validationErrors[] = $rowError;
                    }
                }
                $totalRows++;
            }

        } finally {
            fclose($file);
        }

        $countValidRows = $totalRows - $countInvalidRows;

        return [
            'headers' => $headers,
            'totalRows' => $totalRows,
            'validationErrors' => $validationErrors,
            'invalidRows' => $countInvalidRows,
            'validRows' => $countValidRows,
            'missingHeaders' => $missing_headers,
            'unknownHeaders' => $unknown_headers,
            'duplicateHeaders' => $duplicate_headers,
            'structureErrors' => false
        ];
    }
}
