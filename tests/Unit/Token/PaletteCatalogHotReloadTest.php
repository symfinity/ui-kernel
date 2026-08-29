<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

final class PaletteCatalogHotReloadTest extends TestCase
{
    private string $configPath;

    protected function setUp(): void
    {
        PaletteCatalog::reset();
        $this->configPath = dirname(__DIR__, 3) . '/config/packages/symfinity_ui_kernel.yaml';
    }

    protected function tearDown(): void
    {
        PaletteCatalog::reset();
    }

    #[Test]
    public function reloadsGeneratorConfigWhenBundleYamlMtimeChanges(): void
    {
        $original = file_get_contents($this->configPath);
        self::assertIsString($original);

        PaletteCatalog::lBounds();

        $mutated = preg_replace(
            '/(?<![_a-z])l_bounds:\s*\[[^\]]+\]/',
            'l_bounds: [0.01, 0.75]',
            $original,
            1,
        );
        if ($mutated === $original) {
            $mutated = preg_replace(
                '/(\n  generator:\n    palette:\n      revision: 1\n)/',
                "$1      l_bounds: [0.01, 0.75]\n",
                $original,
                1,
            );
        }
        self::assertIsString($mutated);

        file_put_contents($this->configPath, $mutated);
        clearstatcache(true, $this->configPath);

        self::assertSame([0.01, 0.75], PaletteCatalog::lBounds(), 'mtime change must invalidate static cache');

        file_put_contents($this->configPath, $original);
        clearstatcache(true, $this->configPath);
        PaletteCatalog::reset();
    }

    #[Test]
    public function blue50IsNotNearWhiteWithShippedGeneratorDefaults(): void
    {
        $generator = new PaletteGenerator();
        $recipe = ThemePaletteRecipe::fromPaletteDefinition(
            ThemePaletteRecipe::baseline()->hueBase(),
            ThemePaletteRecipe::baseline()->monoTones(),
        );

        $tuple = $generator->resolveToOklch('blue.50', $recipe);

        self::assertLessThan(0.95, $tuple->l, 'level 50 must not map to near-white L');
        self::assertGreaterThan(0.015, $tuple->c, 'level 50 must retain perceptible chroma');
    }
}
