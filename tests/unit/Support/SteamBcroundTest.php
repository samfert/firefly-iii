<?php

declare(strict_types=1);

namespace Tests\unit\Support;

use FireflyIII\Support\Steam;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\integration\TestCase;

final class SteamBcroundTest extends TestCase
{
    private Steam $steam;

    protected function setUp(): void
    {
        parent::setUp();
        $this->steam = new Steam();
    }

    #[DataProvider('provideNumbers')]
    public function testBcroundReturnsExpectedValue(?string $input, int $precision, string $expected): void
    {
        $result = $this->steam->bcround($input, $precision);
        $this->assertSame($expected, $result);
    }

    public static function provideNumbers(): iterable
    {
        yield 'null input' => [null, 2, '0'];

        yield 'empty string' => ['', 2, '0'];

        yield 'whitespace only' => ['   ', 2, '0'];

        yield 'integer no decimals' => ['123', 2, '123'];

        yield 'positive round up 2 places' => ['123.456', 2, '123.46'];

        yield 'positive round down 2 places' => ['123.444', 2, '123.44'];

        yield 'negative round up 2 places' => ['-123.456', 2, '-123.46'];

        yield 'negative round down 2 places' => ['-123.444', 2, '-123.44'];

        yield 'scientific notation simple' => ['1.23e2', 0, '123'];

        yield 'scientific notation with precision' => ['1.2345e1', 2, '12.35'];

        yield 'scientific notation negative' => ['-1.23e2', 1, '-123.0'];

        yield 'round to 0 places' => ['123.789', 0, '124'];

        yield 'round negative to 0 places' => ['-123.789', 0, '-124'];

        yield 'very small positive' => ['0.001', 2, '0.00'];

        yield 'very small negative' => ['-0.001', 2, '0.00'];

        yield 'zero with precision' => ['0', 2, '0'];

        yield 'zero string with precision' => ['0.00', 2, '0.00'];
    }
}
