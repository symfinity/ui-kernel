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
    }

    #[Test]
    public function p3BoostsSkipFrozenHexAnchorsToPreserveHue(): void
    {
        $tokens = ThemeDtcgResolverFactory::create()->resolve(ThemeCatalog::variant('default'))->all();
        $boosts = (new SemanticColorDerivatives())->p3Boosts($tokens);
        $keys = array_column($boosts, 'key');

        self::assertNotContains('--ui-color-primary', $keys);
        self::assertNotContains('--ui-color-danger', $keys);
        self::assertNotContains('--ui-color-success', $keys);
        self::assertNotContains('--ui-color-warning', $keys);
    }
}
