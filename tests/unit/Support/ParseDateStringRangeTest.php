<?php

declare(strict_types=1);

namespace Tests\unit\Support;

use FireflyIII\Support\ParseDateString;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\integration\TestCase;

final class ParseDateStringRangeTest extends TestCase
{
    private ParseDateString $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new ParseDateString();
    }

    #[DataProvider('provideDateRangeValidCases')]
    public function testIsDateRangeReturnsTrueForValidRanges(string $date): void
    {
        $this->assertTrue($this->parser->isDateRange($date));
    }

    public static function provideDateRangeValidCases(): iterable
    {
        yield 'day range' => ['xxxx-xx-15'];

        yield 'month range' => ['xxxx-12-xx'];

        yield 'year range' => ['2020-xx-xx'];

        yield 'month and day range' => ['xxxx-03-25'];

        yield 'day and year range' => ['2023-xx-31'];

        yield 'month and year range' => ['2022-05-xx'];
    }

    #[DataProvider('provideDateRangeInvalidCases')]
    public function testIsDateRangeReturnsFalseForInvalidCases(string $date): void
    {
        $this->assertFalse($this->parser->isDateRange($date));
    }

    public static function provideDateRangeInvalidCases(): iterable
    {
        yield 'complete date' => ['2024-12-25'];

        yield 'all x' => ['xxxx-xx-xx'];

        yield 'wrong length' => ['xxxx-xx-xxx'];

        yield 'too short' => ['xxxx-xx-'];

        yield 'not 10 chars' => ['2024-12-2'];

        yield 'no x at all' => ['2024-12-25'];
    }

    public function testParseRangeDayOnly(): void
    {
        $result = $this->parser->parseRange('xxxx-xx-09');
        $this->assertSame(['day' => '09'], $result);
    }

    public function testParseRangeMonthOnly(): void
    {
        $result = $this->parser->parseRange('xxxx-11-xx');
        $this->assertSame(['month' => '11'], $result);
    }

    public function testParseRangeYearOnly(): void
    {
        $result = $this->parser->parseRange('2020-xx-xx');
        $this->assertSame(['year' => '2020'], $result);
    }

    public function testParseRangeMonthAndDay(): void
    {
        $result = $this->parser->parseRange('xxxx-02-28');
        $this->assertSame(['month' => '02', 'day' => '28'], $result);
    }

    public function testParseRangeDayAndYear(): void
    {
        $result = $this->parser->parseRange('2023-xx-31');
        $this->assertSame(['year' => '2023', 'day' => '31'], $result);
    }

    public function testParseRangeMonthAndYear(): void
    {
        $result = $this->parser->parseRange('2022-05-xx');
        $this->assertSame(['year' => '2022', 'month' => '05'], $result);
    }

    #[DataProvider('provideDayRangeCases')]
    public function testParseDayRange(string $input, string $expectedDay): void
    {
        $result = $this->parser->parseRange($input);
        $this->assertArrayHasKey('day', $result);
        $this->assertSame($expectedDay, $result['day']);
    }

    public static function provideDayRangeCases(): iterable
    {
        yield 'first of month' => ['xxxx-xx-01', '01'];

        yield 'mid month' => ['xxxx-xx-15', '15'];

        yield 'end of month' => ['xxxx-xx-31', '31'];
    }

    #[DataProvider('provideMonthRangeCases')]
    public function testParseMonthRange(string $input, string $expectedMonth): void
    {
        $result = $this->parser->parseRange($input);
        $this->assertArrayHasKey('month', $result);
        $this->assertSame($expectedMonth, $result['month']);
    }

    public static function provideMonthRangeCases(): iterable
    {
        yield 'January' => ['xxxx-01-xx', '01'];

        yield 'June' => ['xxxx-06-xx', '06'];

        yield 'December' => ['xxxx-12-xx', '12'];
    }

    #[DataProvider('provideYearRangeCases')]
    public function testParseYearRange(string $input, string $expectedYear): void
    {
        $result = $this->parser->parseRange($input);
        $this->assertArrayHasKey('year', $result);
        $this->assertSame($expectedYear, $result['year']);
    }

    public static function provideYearRangeCases(): iterable
    {
        yield '20th century' => ['1999-xx-xx', '1999'];

        yield '21st century early' => ['2000-xx-xx', '2000'];

        yield '21st century recent' => ['2024-xx-xx', '2024'];
    }
}
