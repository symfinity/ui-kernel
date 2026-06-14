<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Contract;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Contract\Exception\ReferenceCycleException;
use Symfinity\UiKernel\Contract\Exception\TokenTypeMismatchException;
use Symfinity\UiKernel\Contract\Exception\UnresolvableAliasException;
use Symfinity\UiKernel\Contract\Token\TokenPath;
use Symfinity\UiKernel\Contract\Token\TokenType;

final class ExceptionsTest extends TestCase
{
    #[Test]
    public function unresolvableAliasNamesThePaths(): void
    {
        $e = new UnresolvableAliasException(TokenPath::fromString('color.primary'), TokenPath::fromString('color.ghost'));

        self::assertStringContainsString('color.primary', $e->getMessage());
        self::assertStringContainsString('color.ghost', $e->getMessage());
        self::assertSame('color.primary', (string) $e->offendingPath);
    }

    #[Test]
    public function referenceCycleNamesTheCycle(): void
    {
        $e = new ReferenceCycleException(['a', 'b', 'a']);

        self::assertStringContainsString('a -> b -> a', $e->getMessage());
        self::assertSame(['a', 'b', 'a'], $e->cycle);
    }

    #[Test]
    public function typeMismatchNamesPathAndTypes(): void
    {
        $e = new TokenTypeMismatchException(TokenPath::fromString('color.primary'), TokenType::Color, TokenType::Dimension);

        self::assertStringContainsString('color.primary', $e->getMessage());
        self::assertStringContainsString('color', $e->getMessage());
        self::assertStringContainsString('dimension', $e->getMessage());
    }
}
