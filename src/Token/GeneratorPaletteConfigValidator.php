<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Validates bundle generator.palette shape (OKLCH curves + hue_chroma only).
 */
final class GeneratorPaletteConfigValidator
{
    private const ALLOWED_KEYS = [
        'interpolation',
        'revision',
        'lightness_curve',
        'hue_chroma',
    ];

    /**
     * @param array<string, mixed> $contractPalette
     * @param array<string, mixed> $generatorPalette
     */
    public static function validate(array $contractPalette, array $generatorPalette): void
    {
        foreach (array_keys($generatorPalette) as $key) {
            if (!in_array($key, self::ALLOWED_KEYS, true)) {
                throw new \RuntimeException(sprintf(
                    'generator.palette.%s is not allowed; allowed keys: %s.',
                    $key,
                    implode(', ', self::ALLOWED_KEYS),
                ));
            }
        }

        $interpolation = $generatorPalette['interpolation'] ?? null;
        if ($interpolation !== 'oklch') {
            throw new \RuntimeException('generator.palette.interpolation must be "oklch".');
        }

        $contractLevels = $contractPalette['levels'] ?? null;
        if (!is_array($contractLevels) || $contractLevels === [] || !array_is_list($contractLevels)) {
            throw new \RuntimeException('contract.palette.levels must be a non-empty list.');
        }

        $levelCount = count($contractLevels);

        $lightnessCurve = $generatorPalette['lightness_curve'] ?? null;
        if (!is_array($lightnessCurve)) {
            throw new \RuntimeException('generator.palette.lightness_curve must be a mapping.');
        }

        foreach ($lightnessCurve as $curveName => $values) {
            if (!is_string($curveName)) {
                throw new \RuntimeException('generator.palette.lightness_curve keys must be strings.');
            }

            if (!is_array($values) || !array_is_list($values)) {
                throw new \RuntimeException(sprintf(
                    'generator.palette.lightness_curve.%s must be a list.',
                    $curveName,
                ));
            }

            if (count($values) !== $levelCount) {
                throw new \RuntimeException(sprintf(
                    'generator.palette.lightness_curve.%s length (%d) must match contract.palette.levels (%d).',
                    $curveName,
                    count($values),
                    $levelCount,
                ));
            }
        }

        $contractHues = $contractPalette['hues'] ?? null;
        if (!is_array($contractHues) || $contractHues === [] || !array_is_list($contractHues)) {
            throw new \RuntimeException('contract.palette.hues must be a non-empty list.');
        }

        foreach ($contractHues as $hue) {
            if (!is_string($hue)) {
                throw new \RuntimeException('contract.palette.hues must contain strings.');
            }
        }

        /** @var list<string> $contractHues */
        $hueChroma = $generatorPalette['hue_chroma'] ?? null;
        if (!is_array($hueChroma)) {
            throw new \RuntimeException('generator.palette.hue_chroma must be a mapping.');
        }

        $chromaKeys = array_keys($hueChroma);
        sort($contractHues);
        $sortedChromaKeys = $chromaKeys;
        sort($sortedChromaKeys);

        if ($sortedChromaKeys !== $contractHues) {
            $unknown = array_diff($chromaKeys, $contractHues);
            if ($unknown !== []) {
                throw new \RuntimeException(sprintf(
                    'generator.palette.hue_chroma has unknown keys: %s.',
                    implode(', ', $unknown),
                ));
            }

            $missing = array_diff($contractHues, $chromaKeys);
            throw new \RuntimeException(sprintf(
                'generator.palette.hue_chroma missing keys for contract hues: %s.',
                implode(', ', $missing),
            ));
        }
    }
}
