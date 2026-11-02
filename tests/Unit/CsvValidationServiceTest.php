<?php

namespace Unit;

use App\Services\CsvValidationService;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
//use PHPUnit\Framework\TestCase;
use Tests\TestCase;
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

    public  function test_it_throws_exception_when_file_does_not_exist(): void
    {
        Storage::fake('local');

        $service = new CsvValidationService();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Uploaded csv file not found');

        $service->validateFile('imports/missing.csv',[]);


    }

    public function test_it_throws_exception_when_csv_is_empty(): void
    {
        Storage::fake('local');

        Storage::disk('local')->put('imports/empty.csv', '');

        $service = new CsvValidationService();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Csv file is empty');

        $service->validateFile('imports/empty.csv', []);
    }

    public function test_csv_contains_only_headers(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('imports/headers.csv', 'name,age');

        $service = new CsvValidationService();

        $result = $service->validateFile(
            'imports/headers.csv',
            [
                ['name' => 'name', 'type' => 'string'],
                ['age' => 'age', 'type' => 'integer'],
            ]
        );

        $this->assertSame(['name','age'], $result['headers']);
        $this->assertSame(0, $result['totalRows']);
        $this->assertSame([], $result['validationErrors']);
        $this->assertSame(0,$result['invalidRows']);
        $this->assertSame(0,$result['validRows']);

    }

    public function test_it_validates_file_when_all_rows_are_valid(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put(
            'imports/valid.csv',
            "name,age,active\n" .
            "John,25,true\n" .
            "Maria,17,false\n" .
            "Alex,32,true\n"
        );

        $service = new CsvValidationService();

        $result = $service->validateFile(
            'imports/valid.csv',
            [
                ['name' => 'name', 'type' => 'string'],
                ['name' => 'age', 'type' => 'integer'],
                ['name' => 'active', 'type' => 'boolean'],
            ]
        );

        $this->assertSame(['name', 'age', 'active'], $result['headers']);
        $this->assertSame(3, $result['totalRows']);
        $this->assertSame([], $result['validationErrors']);
        $this->assertSame(0,$result['invalidRows']);
        $this->assertSame(3,$result['validRows']);

    }

    public function test_it_accumulates_errors_and_counts_invalid_rows_correctly(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put(
            'imports/invalid.csv',
            "name,age,active\n" .
            "John,25,true\n" .
            "Maria,seventeen,no\n" .
            "Alex,32,false\n".
            "Chris,abc,yes"
        );

        $service = new CsvValidationService();
        $result = $service->validateFile(
            'imports/invalid.csv',
            [
                ['name' => 'name', 'type' => 'string'],
                ['name' => 'age', 'type' => 'integer'],
                ['name' => 'active', 'type' => 'boolean'],
            ]
        );

        $this->assertSame(['name', 'age', 'active'], $result['headers']);
        $this->assertSame(4, $result['totalRows']);
        $this->assertSame(2, $result['validRows']);
        $this->assertSame(2, $result['invalidRows']);

        $this->assertCount(4, $result['validationErrors']);

        $errorRows = array_unique(
            array_column($result['validationErrors'], 'row'),
        );
        $this->assertSame([3,5], array_values($errorRows));


    }
}
