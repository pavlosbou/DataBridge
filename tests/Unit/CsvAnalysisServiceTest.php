<?php

namespace Unit;

use App\Services\CsvAnalysisService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class CsvAnalysisServiceTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    #[DataProvider('ColumnTypeCases')]
    public function test_it_infers_column_type(
        array $values,
        string $expectedType
    ): void
    {
        $service = new CsvAnalysisService();

        $method = new \ReflectionMethod(
            CsvAnalysisService::class,
            'inferColumnType'
        );

        $actualType = $method->invoke($service, $values);
        $this->assertSame($expectedType, $actualType);
    }

    public static function columnTypeCases(): array
    {
        return [
            'all integers' => [
                ['25', '17', '32'],
                'integer',
            ],
            'integers and decimals' => [
                ['25', '17.5', '32'],
                'decimal',
            ],
            'integers with empty values' => [
                ['25', '', '32'],
                'integer',
            ],
            'only empty values' => [
                ['', '', ''],
                'string',
            ],
            'mixed integers and text' => [
                ['25', 'unknown', '32'],
                'string',
            ],
            'all booleans' => [
                ['true', 'false', 'true'],
                'boolean',
            ],
            'all dates' => [
                ['2026-10-28', '2026-10-29'],
                'date',
            ],
            'zero and one are integers' => [
                ['0', '1', '0'],
                'integer',
            ],
            'empty values with whitespace' => [
                ['25', '   ', '32'],
                'integer',
            ],
        ];
    }

}
