<?php

namespace Tests\Unit\Support;

use App\Support\Money;
use InvalidArgumentException;
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

    /**
     * @dataProvider invalidInputProvider
     */
    public function testToPenceRejectsMalformedInput(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::toPence($input);
    }

    public static function invalidInputProvider(): array
    {
        return [
            'empty string' => [''],
            'one decimal place' => ['1.5'],
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
