<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Palette;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Token\MonoTone;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\ThemeConfig;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

/**
 * Mono tone tints must be perceptible on surface steps (ui-themer canvas, semantic neutrals).
 */
final class MonoSpiceChromaVisibilityTest extends TestCase
{
    private PaletteGenerator $generator;

    protected function setUp(): void
    {
        PaletteCatalog::reset();
        $this->generator = new PaletteGenerator();
    }

    protected function tearDown(): void
    {
        PaletteCatalog::reset();
    }

    #[Test]
    public function surfaceStepMonoTonesCarryPerceptibleChroma(): void
    {
        $recipe = ThemeConfig::get('semantic')->paletteRecipe();

        foreach ([MonoTone::Cool, MonoTone::Warm, MonoTone::Wood] as $tone) {
            $tuple = $this->generator->monoOklch($tone, 100, $recipe);
            self::assertGreaterThanOrEqual(
                0.005,
                $tuple->c,
                sprintf('mono.%s.100 chroma should tint surfaces', $tone->value),
            );
        }
    }

    #[Test]
    public function coolAndWarmSurfaceTintsDivergeByHue(): void
    {
        $recipe = ThemePaletteRecipe::fromPaletteDefinition(
            ThemeConfig::get('semantic')->paletteRecipe()->hueBase(),
            ThemeConfig::get('semantic')->paletteRecipe()->monoTones(),
        );

        $cool = $this->generator->monoOklch(MonoTone::Cool, 100, $recipe);
        $warm = $this->generator->monoOklch(MonoTone::Warm, 100, $recipe);

        self::assertGreaterThan(30.0, abs($cool->h - $warm->h));
        self::assertGreaterThanOrEqual(0.005, $cool->c);
        self::assertGreaterThanOrEqual(0.005, $warm->c);
        self::assertNotSame(
            $this->generator->monoHex(MonoTone::Cool, 100, $recipe),
            $this->generator->monoHex(MonoTone::Warm, 100, $recipe),
        );
    }
}
