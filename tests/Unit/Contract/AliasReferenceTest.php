<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Contract;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Contract\Token\AliasReference;

final class AliasReferenceTest extends TestCase
{
    #[Test]
    public function itDetectsAliasStrings(): void
    {
        self::assertTrue(AliasReference::isAlias('{color.primary}'));
        self::assertFalse(AliasReference::isAlias('oklch(0.5 0.2 256)'));
        self::assertFalse(AliasReference::isAlias('{}'));
        self::assertFalse(AliasReference::isAlias(['colorSpace' => 'oklch']));
    }

    #[Test]
    public function itParsesTheTargetPath(): void
    {
        $alias = AliasReference::parse('{color.blue.600}');

        self::assertSame('color.blue.600', (string) $alias->target());
        self::assertSame('{color.blue.600}', (string) $alias);
    }

    #[Test]
    public function itRejectsNonAliasStrings(): void
    {
        $this->expectException(InvalidArgumentException::class);

        AliasReference::parse('not-an-alias');
    }
}
