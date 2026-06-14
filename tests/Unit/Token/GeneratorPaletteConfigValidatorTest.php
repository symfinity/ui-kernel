<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Palette\PaletteRampMath;
use Symfinity\UiKernel\Token\GeneratorPaletteConfigValidator;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

final class GeneratorPaletteConfigValidatorTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function validContract(): array
    {
        return [
            'levels' => [100, 200, 300, 400, 500, 600, 700, 800, 900, 950],
            'alpha' => [0, 5, 10, 15, 25, 40, 50, 60, 75, 100],
            'hues' => ['red', 'orange', 'yellow', 'lime', 'green', 'emerald', 'teal', 'cyan', 'sky', 'blue', 'violet', 'purple', 'pink'],
            'forbidden_hues' => [],
            'mono_tones' => ['pure', 'evil', 'warm', 'wood', 'cool', 'pope'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validGenerator(): array
    {
        return [
            'revision' => 1,
        ];
    }

    #[Test]
    public function minimalGeneratorConfigIsValid(): void
    {
        $generator = $this->validGenerator();

        self::assertSame(['revision'], array_keys($generator));

        GeneratorPaletteConfigValidator::validate($this->validContract(), $generator);
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function optionalSparseOverridesAreAllowed(): void
    {
        $generator = [
            'revision' => 1,
            'l_bounds' => [0.01, 0.99],
            'pure_l_bounds' => [1.0, 0.0],
            'chroma_percent' => 85.0,
        ];

        GeneratorPaletteConfigValidator::validate($this->validContract(), $generator);
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function legacyLightnessCurveKeyIsRejected(): void
    {
        $generator = $this->validGenerator();
        $generator['lightness_curve'] = ['default' => [0.9, 0.8]];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('generator.palette.lightness_curve is not allowed');

        GeneratorPaletteConfigValidator::validate($this->validContract(), $generator);
    }

    #[Test]
    public function legacyHueChromaKeyIsRejected(): void
    {
        $generator = $this->validGenerator();
        $generator['hue_chroma'] = ['red' => 0.2];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('generator.palette.hue_chroma is not allowed');

        GeneratorPaletteConfigValidator::validate($this->validContract(), $generator);
    }

    #[Test]
    public function forbiddenLightnessKeyIsRejected(): void
    {
        $generator = $this->validGenerator();
        $generator['lightness'] = ['default' => [89, 80]];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('generator.palette.lightness is not allowed');

        GeneratorPaletteConfigValidator::validate($this->validContract(), $generator);
    }

    #[Test]
    public function forbiddenInterpolationKeyIsRejected(): void
    {
        $generator = $this->validGenerator();
        $generator['interpolation'] = 'oklch';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('generator.palette.interpolation is not allowed');

        GeneratorPaletteConfigValidator::validate($this->validContract(), $generator);
    }
}
