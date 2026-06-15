<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Contract;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Contract\Token\AliasReference;
use Symfinity\UiKernel\Contract\Token\Token;
use Symfinity\UiKernel\Contract\Token\TokenGroup;
use Symfinity\UiKernel\Contract\Token\TokenPath;
use Symfinity\UiKernel\Contract\Token\TokenType;

final class TokenTest extends TestCase
{
    #[Test]
    public function concreteTokenExposesValueTypeAndExtensions(): void
    {
        $token = new Token(
            TokenPath::fromString('color.blue.600'),
            TokenType::Color,
            'oklch(0.55 0.21 256)',
            'primary blue',
            ['org.symfinity' => ['ramp' => 600]],
        );

        self::assertFalse($token->isAlias());
        self::assertSame(TokenType::Color, $token->type());
        self::assertSame('oklch(0.55 0.21 256)', $token->value());
        self::assertSame('primary blue', $token->description());
        self::assertSame(['org.symfinity' => ['ramp' => 600]], $token->extensions());
    }

    #[Test]
    public function aliasTokenIsFlaggedAndCanBeResolved(): void
    {
        $token = new Token(
            TokenPath::fromString('color.primary'),
            TokenType::Color,
            AliasReference::parse('{color.blue.600}'),
        );

        self::assertTrue($token->isAlias());

        $resolved = $token->withResolvedValue('oklch(0.55 0.21 256)', TokenType::Color);

        self::assertFalse($resolved->isAlias());
        self::assertSame('oklch(0.55 0.21 256)', $resolved->value());
        self::assertSame('color.primary', (string) $resolved->path());
    }

    #[Test]
    public function tokenGroupFlattensNestedChildrenInOrder(): void
    {
        $blue = new Token(TokenPath::fromString('color.blue.600'), TokenType::Color, 'oklch(0.55 0.21 256)');
        $primary = new Token(TokenPath::fromString('color.primary'), TokenType::Color, AliasReference::parse('{color.blue.600}'));

        $colorGroup = new TokenGroup(
            TokenType::Color,
            [
                'blue' => new TokenGroup(TokenType::Color, ['level' => $blue]),
                'primary' => $primary,
            ],
        );
        $root = new TokenGroup(null, ['color' => $colorGroup]);

        self::assertSame(['color.blue.600', 'color.primary'], array_keys($root->flatten()));
    }
}
