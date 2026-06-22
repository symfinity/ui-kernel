<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Dtcg\ThemeDtcgResolver;
use Symfinity\UiKernel\Dtcg\LayerStackBuilder;
use Symfinity\UiKernel\Dtcg\DesignSystemLayerRegistry;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\GlassSurfaceTokens;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class GlassSurfaceTokensTest extends TestCase
{
    #[Test]
    public function resolveProducesIndependentBlurFromBackdrop(): void
    {
        $merged = [
            '--ui-color-surface-elevated' => 'oklch(0.9 0 0)',
            '--ui-backdrop-blur' => '6px',
        ];

        $tokens = GlassSurfaceTokens::resolve($merged);

        self::assertSame('12px', $tokens['--ui-glass-blur']);
        self::assertSame('oklch(0.9 0 0)', $tokens['--ui-glass-fallback-surface']);
        self::assertStringContainsString('color-mix(in oklch', $tokens['--ui-glass-tint']);
    }

    #[Test]
    public function allBuiltinThemesIncludeGlassTokens(): void
    {
        $resolver = new ThemeDtcgResolver(
            new LayerStackBuilder(
                new DesignSystemLayerRegistry(
                    DesignSystemLayerRegistry::defaultDirectory(),
                ),
            ),
        );

        foreach (['default', 'default-dark', 'semantic', 'semantic-dark', 'utility', 'utility-dark'] as $id) {
            $tokens = $resolver->resolve(ThemeCatalog::variant($id))->all();
            foreach (ThemeTokenSchema::GLASS_KEYS as $key) {
                self::assertArrayHasKey($key, $tokens, $id . ' missing ' . $key);
            }
            self::assertNotSame($tokens['--ui-glass-blur'], $tokens['--ui-backdrop-blur']);
        }
    }
}
