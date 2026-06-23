<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Contract;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Contract\Layer\LayerRole;
use Symfinity\UiKernel\Contract\Layer\LayerStack;
use Symfinity\UiKernel\Contract\Layer\TokenLayer;
use Symfinity\UiKernel\Contract\Token\Token;
use Symfinity\UiKernel\Contract\Token\TokenPath;
use Symfinity\UiKernel\Contract\Token\TokenType;

final class LayerStackTest extends TestCase
{
    #[Test]
    public function higherPrecedenceLayerReplacesTokenByPath(): void
    {
        $base = new TokenLayer('base', LayerRole::Base, [
            'color.primary' => new Token(TokenPath::fromString('color.primary'), TokenType::Color, 'base'),
            'color.text' => new Token(TokenPath::fromString('color.text'), TokenType::Color, 'base-text'),
        ]);
        $theme = new TokenLayer('zinc', LayerRole::Theme, [
            'color.primary' => new Token(TokenPath::fromString('color.primary'), TokenType::Color, 'theme'),
        ]);

        // Construction order intentionally theme-first to prove precedence, not order, wins.
        $merged = (new LayerStack($theme, $base))->merge();

        self::assertSame('theme', $merged['color.primary']->value());
        self::assertSame('base-text', $merged['color.text']->value());
    }

    #[Test]
    public function overrideKeepsPositionWhileNewKeysAppend(): void
    {
        $base = new TokenLayer('base', LayerRole::Base, [
            'color.primary' => new Token(TokenPath::fromString('color.primary'), TokenType::Color, 'base'),
            'color.text' => new Token(TokenPath::fromString('color.text'), TokenType::Color, 'base-text'),
        ]);
        $ds = new TokenLayer('symfinity', LayerRole::DesignSystem, [
            'color.primary' => new Token(TokenPath::fromString('color.primary'), TokenType::Color, 'ds'),
            'color.accent' => new Token(TokenPath::fromString('color.accent'), TokenType::Color, 'ds-accent'),
        ]);

        $merged = (new LayerStack($base, $ds))->merge();

        self::assertSame(['color.primary', 'color.text', 'color.accent'], array_keys($merged));
    }

    #[Test]
    public function signatureChangesWithLayerIdentity(): void
    {
        $base = new TokenLayer('base', LayerRole::Base, []);
        $themeA = new TokenLayer('zinc', LayerRole::Theme, []);
        $themeB = new TokenLayer('slate', LayerRole::Theme, []);

        $sigA = (new LayerStack($base, $themeA))->signature();
        $sigB = (new LayerStack($base, $themeB))->signature();

        self::assertNotSame($sigA, $sigB);
        self::assertSame($sigA, (new LayerStack($base, $themeA))->signature());
    }
}
