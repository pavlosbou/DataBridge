<?php

namespace App\Services;

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
}
