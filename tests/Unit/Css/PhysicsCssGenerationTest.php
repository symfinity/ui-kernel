<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Css\PhysicsCssEmitter;
use Symfinity\UiKernel\Dtcg\BuiltinDtcgThemeCatalog;
use Symfinity\UiKernel\Theme\EffectivePhysicsResolver;
use Symfinity\UiKernel\Theme\PhysicsId;
use Symfinity\UiKernel\Theme\PhysicsRegistry;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class PhysicsCssGenerationTest extends TestCase
{
    #[Test]
    public function emitterProducesThreePhysicsBlocksWithRequiredTokens(): void
    {
        $css = (new PhysicsCssEmitter())->emit();

        foreach (['flat', 'glass', 'retro'] as $id) {
            self::assertStringContainsString(sprintf('[data-ui-physics="%s"]', $id), $css);
        }

        foreach (PhysicsRegistry::PHYSICS_TOKEN_KEYS as $key) {
            self::assertStringContainsString($key, $css);
        }

        self::assertStringContainsString('--ui-motion-duration-normal: var(--ui-physics-motion-duration-normal)', $css);
        self::assertStringContainsString('--ui-radius-md: var(--ui-physics-radius-md)', $css);
    }

    #[Test]
    public function cssGeneratorAppendsPhysicsBlocksForBuiltinTheme(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V1_0);

        self::assertStringContainsString('[data-ui-physics="flat"]', $css);
        self::assertStringContainsString('[data-ui-physics="glass"]', $css);
        self::assertStringContainsString('[data-ui-physics="retro"]', $css);
        self::assertStringContainsString('[data-theme="semantic"]', $css);
    }

    #[Test]
    public function lightThemeWithGlassPhysicsCorrectsToFlat(): void
    {
        $catalog = BuiltinDtcgThemeCatalog::fromDefaultDirectory();
        $variant = $catalog->get('default');
        $resolution = (new EffectivePhysicsResolver())->resolve(PhysicsId::Glass, $variant->isDarkVariant());

        self::assertTrue($resolution->corrected);
        self::assertSame(PhysicsId::Flat, $resolution->effective);
    }

    #[Test]
    public function darkThemeWithGlassPhysicsStaysGlass(): void
    {
        $catalog = BuiltinDtcgThemeCatalog::fromDefaultDirectory();
        $variant = $catalog->get('default-dark');
        $resolution = (new EffectivePhysicsResolver())->resolve(PhysicsId::Glass, $variant->isDarkVariant());

        self::assertFalse($resolution->corrected);
        self::assertSame(PhysicsId::Glass, $resolution->effective);
    }
}
