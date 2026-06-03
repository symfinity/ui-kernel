<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Validates internal palette reference strings (018 SSOT).
 *
 * @internal
 */
final class PaletteRefGrammar
{
    public static function assertValid(string $ref): void
    {
        if ($ref === '') {
            ThemeErrorCatalog::throw(
                ThemeErrorCatalog::INVALID_PALETTE_REF,
                'Palette ref must not be empty.',
            );
        }

        $base = $ref;
        $alpha = null;
        if (str_contains($ref, '@')) {
            [$base, $alphaToken] = explode('@', $ref, 2);
            if ($alphaToken === '' || !ctype_digit($alphaToken)) {
                ThemeErrorCatalog::throw(
                    ThemeErrorCatalog::INVALID_PALETTE_REF,
                    sprintf('Invalid alpha modifier in ref "%s".', $ref),
                );
            }
            $alpha = (int) $alphaToken;
            if (!in_array($alpha, PaletteCatalog::alphaPercent(), true)) {
                ThemeErrorCatalog::throw(
                    ThemeErrorCatalog::INVALID_PALETTE_REF,
                    sprintf('Alpha "%d" is not allowed on ref "%s".', $alpha, $ref),
                );
            }
        }

        foreach (PaletteCatalog::forbiddenHues() as $forbidden) {
            if (str_starts_with($base, $forbidden . '.')) {
                ThemeErrorCatalog::throw(
                    ThemeErrorCatalog::INVALID_PALETTE_REF,
                    sprintf('Hue "%s" is not in the contract (ref "%s").', $forbidden, $ref),
                );
            }
        }

        if (preg_match('/\.\d+[a-z]/', $base) === 1) {
            ThemeErrorCatalog::throw(
                ThemeErrorCatalog::INVALID_PALETTE_REF,
                sprintf('Level suffix letters are forbidden (ref "%s").', $ref),
            );
        }

        if (preg_match('/^mono\.([a-z]+)\.(\d+)$/', $base, $matches) === 1) {
            try {
                MonoTone::from($matches[1]);
            } catch (\ValueError) {
                ThemeErrorCatalog::throw(
                    ThemeErrorCatalog::INVALID_PALETTE_REF,
                    sprintf('Unknown mono tone in ref "%s".', $ref),
                );
            }
            self::assertLevel((int) $matches[2], $ref);

            return;
        }

        if (preg_match('/^([a-z]+)\.(\d+)$/', $base, $matches) === 1) {
            $hue = $matches[1];
            if (!in_array($hue, PaletteCatalog::hues(), true)) {
                ThemeErrorCatalog::throw(
                    ThemeErrorCatalog::INVALID_PALETTE_REF,
                    sprintf('Unknown hue "%s" in ref "%s".', $hue, $ref),
                );
            }
            self::assertLevel((int) $matches[2], $ref);

            return;
        }

        ThemeErrorCatalog::throw(
            ThemeErrorCatalog::INVALID_PALETTE_REF,
            sprintf('Invalid palette ref "%s".', $ref),
        );
    }

    private static function assertLevel(int $level, string $ref): void
    {
        if (!in_array($level, PaletteCatalog::levels(), true)) {
            ThemeErrorCatalog::throw(
                ThemeErrorCatalog::INVALID_PALETTE_REF,
                sprintf('Level %d is not in the contract ramp (ref "%s").', $level, $ref),
            );
        }
    }
}
