<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Contract;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Contract\Token\TokenPath;

final class TokenPathTest extends TestCase
{
    #[Test]
    public function itParsesAndStringifiesADottedPath(): void
    {
        $path = TokenPath::fromString('color.blue.600');

        self::assertSame(['color', 'blue', '600'], $path->segments());
        self::assertSame('color.blue.600', (string) $path);
        self::assertSame('color', $path->first());
        self::assertSame(3, $path->depth());
    }

    #[Test]
    public function equalityComparesSegments(): void
    {
        self::assertTrue(TokenPath::fromString('color.primary')->equals(TokenPath::fromString('color.primary')));
        self::assertFalse(TokenPath::fromString('color.primary')->equals(TokenPath::fromString('color.secondary')));
    }

    #[Test]
    public function childExtendsThePath(): void
    {
        self::assertSame('color.primary', (string) TokenPath::fromString('color')->child('primary'));
    }

    #[Test]
    public function itRejectsInvalidSegments(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TokenPath::fromString('color..primary');
    }

    #[Test]
    public function itRejectsSegmentsWithIllegalCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TokenPath::fromString('color.pri mary');
    }
}
