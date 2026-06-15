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
        'mono_hues',
        'dark_tail_l_end',
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

        $monoTones = $contractPalette['mono_tones'] ?? null;
        if (!is_array($monoTones) || $monoTones === [] || !array_is_list($monoTones)) {
            throw new \RuntimeException('contract.palette.mono_tones must be a non-empty list.');
        }

        $monoToneNames = [];
        foreach ($monoTones as $tone) {
            if (!is_string($tone)) {
                throw new \RuntimeException('contract.palette.mono_tones must contain strings.');
            }
            $monoToneNames[] = $tone;
        }

        self::validateMonoHues($generatorPalette['mono_hues'] ?? null, $monoToneNames);

        self::validateBoundsPair($generatorPalette['l_bounds'] ?? null, 'l_bounds');
        self::validateBoundsPair($generatorPalette['pure_l_bounds'] ?? null, 'pure_l_bounds');

        $chromaPercent = $generatorPalette['chroma_percent'] ?? null;
        if ($chromaPercent !== null && !is_numeric($chromaPercent)) {
            throw new \RuntimeException('generator.palette.chroma_percent must be numeric.');
        }

        $darkTailEnd = $generatorPalette['dark_tail_l_end'] ?? null;
        if ($darkTailEnd !== null && !is_numeric($darkTailEnd)) {
            throw new \RuntimeException('generator.palette.dark_tail_l_end must be numeric.');
        }
    }

    /**
     * @param mixed $monoHues
     * @param list<string> $monoTones
     */
    private static function validateMonoHues(mixed $monoHues, array $monoTones): void
    {
        if (!is_array($monoHues)) {
            throw new \RuntimeException('generator.palette.mono_hues must be defined.');
        }

        $keys = array_keys($monoHues);
        sort($keys);
        $expected = $monoTones;
        sort($expected);

        if ($keys !== $expected) {
            throw new \RuntimeException('generator.palette.mono_hues keys must match contract.palette.mono_tones.');
        }

        foreach ($monoHues as $tone => $degrees) {
            if (!is_numeric($degrees)) {
                throw new \RuntimeException(sprintf('generator.palette.mono_hues.%s must be numeric.', (string) $tone));
            }
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

    /**
     * @param array<string, mixed> $palette theme.meta.yaml palette block
     */
    public static function validateThemeMetaPalette(array $palette): void
    {
        if (isset($palette['mono'])) {
            throw new \InvalidArgumentException(
                'Legacy palette.mono blocks are not allowed; use palette.mono_saturation. '
                . 'Rename mono tone ids: pure→neutral, cool→slate, warm→stone, wood→sage, pope→mauve, evil→rust.',
            );
        }

        $saturation = $palette['mono_saturation'] ?? null;
        if (!is_numeric($saturation)) {
            throw new \InvalidArgumentException('Theme palette must define palette.mono_saturation.');
        }
    }

    /**
     * @param list<array<string, mixed>> $variants
     */
    public static function validateVariantTones(array $variants): void
    {
        $allowed = array_merge(PaletteCatalog::monoGrammarTones(), []);
        foreach ($variants as $entry) {
            $tone = $entry['tone'] ?? null;
            if (!is_string($tone) || !in_array($tone, $allowed, true)) {
                throw new \InvalidArgumentException(sprintf(
                    'Variant tone "%s" is invalid; allowed: %s.',
                    is_string($tone) ? $tone : '(missing)',
                    implode(', ', $allowed),
                ));
            }
        }
    }
}
