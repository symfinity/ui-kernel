<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Palette;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

/**
 * Generator palette revision 2 — per-hue in-gamut chroma targets (2026-06-12).
 */
final class PaletteGeneratorVibrancyRetuneTest extends TestCase
{
    /** @var array<string, float> Revision 1 flat/over-shoot map */
    private const REVISION_ONE_HUE_CHROMA_500 = [
        'red' => 0.244,
        'orange' => 0.1935,
        'yellow' => 0.500,
        'lime' => 0.500,
        'green' => 0.333,
        'emerald' => 0.500,
        'teal' => 0.500,
        'cyan' => 0.500,
        'sky' => 0.500,
        'blue' => 0.500,
        'violet' => 0.500,
        'purple' => 0.500,
        'pink' => 0.500,
    ];

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
    public function revisionTwoUsesPerHueTargetsBelowRevisionOneFlatCaps(): void
    {
        foreach (PaletteCatalog::hueFamilies() as $hue) {
            $current = PaletteCatalog::hueChroma($hue);
            $revisionOne = self::REVISION_ONE_HUE_CHROMA_500[$hue];

            self::assertLessThan(
                $revisionOne,
                $current,
                sprintf('Revision 2 must stay below revision 1 overshoot for %s.', $hue),
            );
            self::assertLessThan(0.37, $current, sprintf('%s target must stay within sRGB OKLCH norms.', $hue));
        }
    }

    #[Test]
    public function hue500RampsDoNotChannelClipToPureRed(): void
    {
        $recipe = ThemePaletteRecipe::baseline();

        foreach (PaletteCatalog::hueFamilies() as $hue) {
            $hex = strtolower($this->generator->resolve($hue . '.500', $recipe));
            self::assertNotSame(
                '#ff0000',
                $hex,
                sprintf('%s.500 must not channel-clip to pure red.', $hue),
            );
        }
    }
}
