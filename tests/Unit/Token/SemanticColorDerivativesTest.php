<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Tests\Support\ThemeDtcgResolverFactory;
use Symfinity\UiKernel\Token\SemanticColorDerivatives;
use Symfinity\UiKernel\Token\ThemeConfig;

final class SemanticColorDerivativesTest extends TestCase
{
    #[Test]
    public function itDerivesOnHoverAndActiveTokensForSemanticColours(): void
    {
        $resolver = ThemeDtcgResolverFactory::create();
        $base = $resolver->resolve(ThemeCatalog::variant('semantic'))->all();

        $derived = (new SemanticColorDerivatives())->derive($base);

        self::assertArrayHasKey('--ui-color-on-primary', $derived);
        self::assertArrayHasKey('--ui-color-primary-hover', $derived);
        self::assertArrayHasKey('--ui-color-primary-active', $derived);
        self::assertMatchesRegularExpression('/^oklch\(/', $derived['--ui-color-on-primary']);
        self::assertSame(
            'oklch(from var(--ui-color-primary) calc(l * 0.88) c h)',
            $derived['--ui-color-primary-hover'],
        );
    }

    #[Test]
    public function resolvedThemeIncludesDerivedTokens(): void
    {
        $tokens = ThemeDtcgResolverFactory::create()->resolve(ThemeCatalog::variant('semantic'))->all();

        self::assertArrayHasKey('--ui-color-on-danger', $tokens);
        self::assertArrayHasKey('--ui-color-warning-active', $tokens);
        self::assertArrayHasKey('--ui-color-on-muted', $tokens);
        self::assertSame('oklch(1 0 0)', $tokens['--ui-color-button-text']);
    }

    #[Test]
    public function buttonTextStaysWhiteForDarkSchemeTokens(): void
    {
        $resolver = ThemeDtcgResolverFactory::create();
        $light = $resolver->resolve(ThemeCatalog::variant('semantic'))->all();
        $dark = $resolver->resolve(ThemeCatalog::variant('semantic-dark'))->all();

        self::assertSame('oklch(1 0 0)', $light['--ui-color-button-text']);
        self::assertSame('oklch(1 0 0)', $dark['--ui-color-button-text']);
    }

    #[Test]
    public function p3BoostsApplyToLiveOklchSemanticColours(): void
    {
        $tokens = ThemeDtcgResolverFactory::create()->resolve(ThemeCatalog::variant('semantic'))->all();
        $keys = array_column((new SemanticColorDerivatives())->p3Boosts($tokens), 'key');

        self::assertContains('--ui-color-primary', $keys);
        self::assertContains('--ui-color-danger', $keys);
    }
}
