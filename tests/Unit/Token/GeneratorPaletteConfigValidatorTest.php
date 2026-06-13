<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Token\GeneratorPaletteConfigValidator;

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
            'interpolation' => 'oklch',
            'revision' => 1,
            'lightness_curve' => [
                'default' => [0.89, 0.80, 0.71, 0.62, 0.53, 0.44, 0.35, 0.26, 0.17, 0.08],
                'pure' => [1.00, 0.89, 0.78, 0.67, 0.56, 0.45, 0.34, 0.23, 0.12, 0.00],
            ],
            'hue_chroma' => [
                'red' => 0.177,
                'orange' => 0.169,
                'yellow' => 0.156,
                'lime' => 0.181,
                'green' => 0.199,
                'emerald' => 0.142,
                'teal' => 0.140,
                'cyan' => 0.179,
                'sky' => 0.189,
                'blue' => 0.199,
                'violet' => 0.189,
                'purple' => 0.185,
                'pink' => 0.168,
            ],
        ];
    }

    #[Test]
    public function bundleConfigKeysMatchAllowedSet(): void
    {
        $generator = $this->validGenerator();

        self::assertSame(
            ['interpolation', 'revision', 'lightness_curve', 'hue_chroma'],
            array_keys($generator),
        );

        GeneratorPaletteConfigValidator::validate($this->validContract(), $generator);
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function invalidLightnessCurveLengthIncludesCurveName(): void
    {
        /** @var array<string, mixed> $generator */
        $generator = $this->validGenerator();
        /** @var array<string, list<float>> $lightnessCurve */
        $lightnessCurve = $generator['lightness_curve'];
        $lightnessCurve['default'] = [0.89, 0.80];
        $generator['lightness_curve'] = $lightnessCurve;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('generator.palette.lightness_curve.default length (2) must match contract.palette.levels (10).');

        GeneratorPaletteConfigValidator::validate($this->validContract(), $generator);
    }

    #[Test]
    public function unknownHueChromaKeyFailsValidation(): void
    {
        /** @var array<string, mixed> $generator */
        $generator = $this->validGenerator();
        /** @var array<string, float> $hueChroma */
        $hueChroma = $generator['hue_chroma'];
        $hueChroma['amber'] = 0.12;
        $generator['hue_chroma'] = $hueChroma;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('generator.palette.hue_chroma has unknown keys: amber.');

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
    public function forbiddenRampLevelsKeyIsRejected(): void
    {
        $generator = $this->validGenerator();
        $generator['ramp_levels'] = [100, 500];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('generator.palette.ramp_levels is not allowed');

        GeneratorPaletteConfigValidator::validate($this->validContract(), $generator);
    }
}
