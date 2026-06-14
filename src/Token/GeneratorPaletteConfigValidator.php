<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Validates bundle generator.palette shape (minimal computed-ramp policy only).
 */
final class GeneratorPaletteConfigValidator
{
    private const ALLOWED_KEYS = [
        'revision',
        'l_bounds',
        'pure_l_bounds',
        'chroma_percent',
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

        $revision = $generatorPalette['revision'] ?? null;
        if ($revision !== 1 && $revision !== '1') {
            throw new \RuntimeException('generator.palette.revision must be 1.');
        }

        $contractLevels = $contractPalette['levels'] ?? null;
        if (!is_array($contractLevels) || $contractLevels === [] || !array_is_list($contractLevels)) {
            throw new \RuntimeException('contract.palette.levels must be a non-empty list.');
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

        self::validateBoundsPair($generatorPalette['l_bounds'] ?? null, 'l_bounds');
        self::validateBoundsPair($generatorPalette['pure_l_bounds'] ?? null, 'pure_l_bounds');

        $chromaPercent = $generatorPalette['chroma_percent'] ?? null;
        if ($chromaPercent !== null && !is_numeric($chromaPercent)) {
            throw new \RuntimeException('generator.palette.chroma_percent must be numeric.');
        }
    }

    /**
     * @param mixed $bounds
     */
    private static function validateBoundsPair(mixed $bounds, string $key): void
    {
        if ($bounds === null) {
            return;
        }

        if (!is_array($bounds) || !array_is_list($bounds) || count($bounds) !== 2) {
            throw new \RuntimeException(sprintf('generator.palette.%s must be a two-float list.', $key));
        }

        foreach ($bounds as $index => $value) {
            if (!is_numeric($value)) {
                throw new \RuntimeException(sprintf('generator.palette.%s[%d] must be numeric.', $key, $index));
            }
        }
    }
}
