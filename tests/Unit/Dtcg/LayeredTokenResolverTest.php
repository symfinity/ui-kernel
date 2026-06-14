<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Dtcg;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Contract\Exception\ReferenceCycleException;
use Symfinity\UiKernel\Contract\Exception\TokenTypeMismatchException;
use Symfinity\UiKernel\Contract\Exception\UnresolvableAliasException;
use Symfinity\UiKernel\Contract\Layer\LayerRole;
use Symfinity\UiKernel\Contract\Layer\LayerStack;
use Symfinity\UiKernel\Contract\Layer\TokenLayer;
use Symfinity\UiKernel\Contract\Token\AliasReference;
use Symfinity\UiKernel\Contract\Token\Token;
use Symfinity\UiKernel\Contract\Token\TokenPath;
use Symfinity\UiKernel\Contract\Token\TokenType;
use Symfinity\UiKernel\Dtcg\LayeredTokenResolver;

final class LayeredTokenResolverTest extends TestCase
{
    private function token(string $path, TokenType $type, mixed $value): Token
    {
        $value = \is_string($value) && AliasReference::isAlias($value) ? AliasReference::parse($value) : $value;

        return new Token(TokenPath::fromString($path), $type, $value);
    }

    #[Test]
    public function themeOverridesDesignSystemOverridesBase(): void
    {
        $base = new TokenLayer('base', LayerRole::Base, [
            'color.primary' => $this->token('color.primary', TokenType::Color, 'base'),
            'color.text' => $this->token('color.text', TokenType::Color, 'base-text'),
        ]);
        $ds = new TokenLayer('chameleon', LayerRole::DesignSystem, [
            'color.primary' => $this->token('color.primary', TokenType::Color, 'ds'),
        ]);
        $theme = new TokenLayer('zinc', LayerRole::Theme, [
            'color.primary' => $this->token('color.primary', TokenType::Color, 'theme'),
        ]);

        $graph = (new LayeredTokenResolver())->resolve(new LayerStack($base, $ds, $theme));

        self::assertSame('theme', $graph->get('color.primary')->value());
        self::assertSame('base-text', $graph->get('color.text')->value());
    }

    #[Test]
    public function aliasChainResolvesToConcretePrimitive(): void
    {
        $base = new TokenLayer('base', LayerRole::Base, [
            'color.blue.600' => $this->token('color.blue.600', TokenType::Color, 'oklch(0.55 0.21 256)'),
            'color.brand' => $this->token('color.brand', TokenType::Color, '{color.blue.600}'),
            'color.primary' => $this->token('color.primary', TokenType::Color, '{color.brand}'),
        ]);

        $graph = (new LayeredTokenResolver())->resolve(new LayerStack($base));

        self::assertFalse($graph->get('color.primary')->isAlias());
        self::assertSame('oklch(0.55 0.21 256)', $graph->get('color.primary')->value());
    }

    #[Test]
    public function unresolvableAliasRaisesLocatedError(): void
    {
        $base = new TokenLayer('base', LayerRole::Base, [
            'color.primary' => $this->token('color.primary', TokenType::Color, '{color.ghost}'),
        ]);

        $this->expectException(UnresolvableAliasException::class);
        $this->expectExceptionMessage('color.primary');

        (new LayeredTokenResolver())->resolve(new LayerStack($base));
    }

    #[Test]
    public function referenceCycleRaisesError(): void
    {
        $base = new TokenLayer('base', LayerRole::Base, [
            'color.a' => $this->token('color.a', TokenType::Color, '{color.b}'),
            'color.b' => $this->token('color.b', TokenType::Color, '{color.a}'),
        ]);

        $this->expectException(ReferenceCycleException::class);

        (new LayeredTokenResolver())->resolve(new LayerStack($base));
    }

    #[Test]
    public function typeMismatchRaisesError(): void
    {
        $base = new TokenLayer('base', LayerRole::Base, [
            'space.4' => $this->token('space.4', TokenType::Dimension, '1rem'),
            'color.primary' => $this->token('color.primary', TokenType::Color, '{space.4}'),
        ]);

        $this->expectException(TokenTypeMismatchException::class);

        (new LayeredTokenResolver())->resolve(new LayerStack($base));
    }
}
