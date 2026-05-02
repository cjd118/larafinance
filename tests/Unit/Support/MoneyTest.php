<?php

namespace Tests\Unit\Support;

use App\Support\Money;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function testToPenceParsesIntegerStrings(): void
    {
        $this->assertSame(0, Money::toPence('0'));
        $this->assertSame(150000, Money::toPence('1500'));
    }

    public function testToPenceParsesDecimalStrings(): void
    {
        $this->assertSame(0, Money::toPence('0.00'));
        $this->assertSame(150, Money::toPence('1.50'));
        $this->assertSame(100001, Money::toPence('1000.01'));
    }

    public function testToPenceParsesSingleDecimalPlaceAsTrailingZero(): void
    {
        // Real Lloyds CSVs sometimes emit a single decimal place (e.g. "13.1"
        // meaning 13 pounds 10 pence). Treat it as if the second digit were 0.
        $this->assertSame(1310, Money::toPence('13.1'));
        $this->assertSame(50, Money::toPence('0.5'));
        $this->assertSame(-50, Money::toPence('-0.5'));
    }

    public function testToPenceParsesNegativeStrings(): void
    {
        $this->assertSame(-5, Money::toPence('-0.05'));
        $this->assertSame(-150000, Money::toPence('-1500'));
        $this->assertSame(-100001, Money::toPence('-1000.01'));
    }

    public function testToPenceHandlesLargeValues(): void
    {
        $this->assertSame(9999999999, Money::toPence('99999999.99'));
    }

    #[DataProvider('invalidInputProvider')]
    public function testToPenceRejectsMalformedInput(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::toPence($input);
    }

    public static function invalidInputProvider(): array
    {
        return [
            'empty string' => [''],
            'three decimal places' => ['1.234'],
            'non-numeric' => ['abc'],
            'leading whitespace' => [' 1.50'],
            'trailing whitespace' => ['1.50 '],
            'leading plus' => ['+1.50'],
            'currency symbol' => ['£1.50'],
            'thousands separator' => ['1,500'],
            'decimal point only' => ['.50'],
            'trailing decimal point' => ['1.'],
        ];
    }
}
