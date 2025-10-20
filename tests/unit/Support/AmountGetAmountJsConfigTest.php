<?php

declare(strict_types=1);

namespace Tests\unit\Support;

use FireflyIII\Support\Amount;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\integration\TestCase;

final class AmountGetAmountJsConfigTest extends TestCase
{
    #[DataProvider('provideFormatConfigurations')]
    public function testGetAmountJsConfigReturnsExpectedFormat(
        bool $sepBySpace,
        int $signPosn,
        string $sign,
        bool $csPrecedes,
        string $expected
    ): void {
        $result = Amount::getAmountJsConfig($sepBySpace, $signPosn, $sign, $csPrecedes);
        $this->assertSame($expected, $result);
    }

    public static function provideFormatConfigurations(): iterable
    {
        yield 'parentheses around all, currency before, with space' => [
            true,
            0,
            '-',
            true,
            '(%s %v)',
        ];

        yield 'parentheses around all, currency before, no space' => [
            false,
            0,
            '-',
            true,
            '(%s%v)',
        ];

        yield 'parentheses around all, currency after, with space' => [
            true,
            0,
            '-',
            false,
            '(%v %s)',
        ];

        yield 'parentheses around all, currency after, no space' => [
            false,
            0,
            '-',
            false,
            '(%v%s)',
        ];

        yield 'sign before all, currency before, with space' => [
            true,
            1,
            '-',
            true,
            '-%s %v',
        ];

        yield 'sign before all, currency before, no space' => [
            false,
            1,
            '-',
            true,
            '-%s%v',
        ];

        yield 'sign before all, currency after, with space' => [
            true,
            1,
            '-',
            false,
            '-%v %s',
        ];

        yield 'sign before all, currency after, no space' => [
            false,
            1,
            '-',
            false,
            '-%v%s',
        ];

        yield 'sign after all, currency before, with space' => [
            true,
            2,
            '-',
            true,
            '%s %v-',
        ];

        yield 'sign after all, currency before, no space' => [
            false,
            2,
            '-',
            true,
            '%s%v-',
        ];

        yield 'sign after all, currency after, with space' => [
            true,
            2,
            '-',
            false,
            '%v %s-',
        ];

        yield 'sign after all, currency after, no space' => [
            false,
            2,
            '-',
            false,
            '%v%s-',
        ];

        yield 'sign before symbol, currency before, with space' => [
            true,
            3,
            '-',
            true,
            '-%s %v',
        ];

        yield 'sign before symbol, currency before, no space' => [
            false,
            3,
            '-',
            true,
            '-%s%v',
        ];

        yield 'sign before symbol, currency after, with space' => [
            true,
            3,
            '-',
            false,
            '%v -%s',
        ];

        yield 'sign before symbol, currency after, no space' => [
            false,
            3,
            '-',
            false,
            '%v-%s',
        ];

        yield 'sign after symbol, currency before, with space' => [
            true,
            4,
            '-',
            true,
            '%s- %v',
        ];

        yield 'sign after symbol, currency before, no space' => [
            false,
            4,
            '-',
            true,
            '%s-%v',
        ];

        yield 'sign after symbol, currency after, with space' => [
            true,
            4,
            '-',
            false,
            '%v %s-',
        ];

        yield 'sign after symbol, currency after, no space' => [
            false,
            4,
            '-',
            false,
            '%v%s-',
        ];

        yield 'positive sign, sign before all' => [
            true,
            1,
            '+',
            true,
            '+%s %v',
        ];

        yield 'custom sign character' => [
            false,
            1,
            '−',
            false,
            '−%v%s',
        ];
    }
}
