<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Palette;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

/**
 * One-shot capture for pre-085 midtone baseline fixture (085 T001).
 *
 * Run explicitly: --filter Pre085BaselineCapture
 */
#[Group('baseline-capture')]
final class Pre085BaselineCaptureTest extends TestCase
{
    #[Test]
    public function capturePre085MidtoneBaseline(): void
    {
        if (!getenv('CAPTURE_PRE085_BASELINE')) {
            self::markTestSkipped('Set CAPTURE_PRE085_BASELINE=1 to regenerate fixture.');
        }

        PaletteCatalog::reset();
        $generator = new PaletteGenerator();
        $recipe = ThemePaletteRecipe::baseline();

        $refs = [];
        foreach (PaletteCatalog::hueFamilies() as $hue) {
            $tuple = $generator->resolveToOklch(sprintf('%s.500', $hue), $recipe);
            $refs[sprintf('%s.500', $hue)] = [
                'l' => round($tuple->l, 6),
                'c' => round($tuple->c, 6),
                'h' => round($tuple->h, 4),
            ];
        }

        foreach (['red', 'yellow', 'blue', 'green', 'orange', 'indigo'] as $hue) {
            foreach ([600, 950] as $level) {
                $tuple = $generator->resolveToOklch(sprintf('%s.%d', $hue, $level), $recipe);
                $refs[sprintf('%s.%d', $hue, $level)] = [
                    'l' => round($tuple->l, 6),
                    'c' => round($tuple->c, 6),
                    'h' => round($tuple->h, 4),
                ];
            }
        }

        $payload = [
            'capture_revision' => 'pre-085',
            'recipe' => 'baseline',
            'refs' => $refs,
        ];

        $path = dirname(__DIR__, 2) . '/Fixtures/palette/pre-085-midtone-baseline.json';
        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

        self::assertFileExists($path);
        self::assertGreaterThan(0, count($refs));
    }
}
