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
            'hues' => ['red', 'orange', 'yellow', 'lime', 'green', 'emerald', 'cyan', 'sky', 'blue', 'violet', 'purple', 'pink'],
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
            'revision' => 2,
            'lightness_curve' => [
                'default' => [0.89, 0.80, 0.71, 0.62, 0.53, 0.44, 0.35, 0.26, 0.17, 0.08],
                'pure' => [1.00, 0.89, 0.78, 0.67, 0.56, 0.45, 0.34, 0.23, 0.12, 0.00],
            ],
            'hue_chroma' => [
                'red' => 0.17,
                'orange' => 0.16,
                'yellow' => 0.14,
                'lime' => 0.15,
                'green' => 0.15,
                'emerald' => 0.14,
                'cyan' => 0.13,
                'sky' => 0.14,
                'blue' => 0.15,
                'violet' => 0.16,
                'purple' => 0.17,
                'pink' => 0.16,
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
        $generator = $this->validGenerator();
        $generator['lightness_curve']['default'] = [0.89, 0.80];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('generator.palette.lightness_curve.default length (2) must match contract.palette.levels (10).');

        GeneratorPaletteConfigValidator::validate($this->validContract(), $generator);
    }

    #[Test]
    public function unknownHueChromaKeyFailsValidation(): void
    {
        $generator = $this->validGenerator();
        $generator['hue_chroma']['teal'] = 0.12;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('generator.palette.hue_chroma has unknown keys: teal.');

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
