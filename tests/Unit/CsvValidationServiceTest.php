<?php

namespace Unit;

use App\Services\CsvValidationService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CsvValidationServiceTest extends TestCase
{

    #[DataProvider('value_type_cases')]
    public function test_validate_value(string $value, string $type, bool $expected): void
    {
        $service = new CsvValidationService();

        $got = $service->validateValue($value, $type);
        $this->assertSame($expected, $got);

    }

    public static function value_type_cases(): array
    {
        return [
            '25 is an integer' => [
                '25',
                'integer',
                true
            ],
            '0 is an integer' => [
                '0',
                'integer',
                true
            ],
            '25.5 is not an integer' => [
                '25.5',
                'integer',
                false
            ],
            '25.5 is a decimal' => [
                '25.5',
                'decimal',
                true
            ],
            'TRUE is a boolean' => [
                'TRUE',
                'boolean',
                true
            ],
            'yes is not a boolean' => [
                'yes',
                'boolean',
                false
            ],
            '2026-10-30 is a date' => [
                '2026-10-30',
                'date',
                true
            ],
            '2026-02-30 is not a date' => [
                '2026-02-30',
                'date',
                false
            ],
            'John is a string' => [
                'John',
                'string',
                true
            ],
            '25 with type unknown is invalid' => [
                '25',
                'unknown',
                false
            ],
            ' empty string could be every type provided' => [
                '',
                'integer',
                true
            ],
            '-10 is an integer' => [
                '-10',
                'integer',
                true
            ],
            '25 can be decimal' => [
                '25',
                'decimal',
                true
            ],
            'John Doe is a valid string' => [
                'John Doe',
                'string',
                true
            ],
            'false is a boolean' => [
                'false',
                'boolean',
                true
            ],
            '1 si not supported as boolean' => [
                '1',
                'boolean',
                false
            ],
            '2026-13-05 is not a valid date' => [
                '2026-13-05',
                'date',
                false
            ],
            ' 25  is a valid integer' => [
                ' 25 ',
                'integer',
                true
            ],
            '  is a valid date' => [
                '',
                'date',
                true
            ],
            ' with type unknown is invalid' => [
                '',
                'unknown',
                false
            ],
            'hello with type integer is invalid as non numeric' => [
                'hello',
                'integer',
                false
            ]
        ];
    }

    #[DataProvider('row_validation_cases')]
    public function test_validate_row(array $row,array $fields, int $rowNumber, array $validationErrors): void
    {
     $service = new CsvValidationService();
     $result = $service->validateRow($row, $fields, $rowNumber);
     $this->assertSame($validationErrors, $result);
    }

    public static function row_validation_cases(): array{
        return [
            'row with 2 invalid fields' => [
                ['Maria', 'unknown', 'yes'],
                [
                    ['name' => 'name', 'type' => 'string'],
                    ['name' => 'age', 'type' => 'integer'],
                    ['name' => 'active', 'type' => 'boolean']
                ],
                3,
                [
                    [
                        'row' => 3,
                        'field' => 'age',
                        'value' => 'unknown',
                        'message' => 'Expected integer',
                    ],
                    [
                        'row' => 3,
                        'field' => 'active',
                        'value' => 'yes',
                        'message' => 'Expected boolean',
                    ],
                ]
            ],
            'row with all valid fields' => [
                ['John', '25', 'true'],

                [
                    ['name' => 'name', 'type' => 'string'],
                    ['name' => 'age', 'type' => 'integer'],
                    ['name' => 'active', 'type' => 'boolean'],
                ],

                2,

                []
            ],
            'row with multiple invalid values' => [
                ['Maria', '25.5', 'yes', '2026-02-30'],

                [
                    ['name' => 'name', 'type' => 'string'],
                    ['name' => 'age', 'type' => 'integer'],
                    ['name' => 'active', 'type' => 'boolean'],
                    ['name' => 'joined_at', 'type' => 'date'],
                ],

                5,

                [
                    [
                        'row' => 5,
                        'field' => 'age',
                        'value' => '25.5',
                        'message' => 'Expected integer',
                    ],
                    [
                        'row' => 5,
                        'field' => 'active',
                        'value' => 'yes',
                        'message' => 'Expected boolean',
                    ],
                    [
                        'row' => 5,
                        'field' => 'joined_at',
                        'value' => '2026-02-30',
                        'message' => 'Expected date',
                    ],
                ],
            ]
        ];
    }
}
