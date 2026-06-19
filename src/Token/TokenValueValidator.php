<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Security boundary for accepted CSS token values (018 FR-019).
 */
final class TokenValueValidator
{
    private const UNSAFE_PATTERN = '/\b(url|expression|@import|javascript:|calc\s*\(\s*var\s*\(\s*--(?!ui-))/i';

    public static function assertValid(string $key, string $value): void
    {
        if ($value === '') {
            ThemeErrorCatalog::throw(
                ThemeErrorCatalog::INVALID_TOKEN_VALUE,
                sprintf('Token "%s" must not be empty.', $key),
            );
        }

        if (preg_match(self::UNSAFE_PATTERN, $value) === 1) {
            ThemeErrorCatalog::throw(
                ThemeErrorCatalog::INVALID_TOKEN_VALUE,
                sprintf('Token "%s" contains a disallowed CSS form.', $key),
            );
        }

        if (self::isBlurKey($key)) {
            self::assertLengthValue($key, $value);

            return;
        }

        if (self::isColorKey($key)) {
            self::assertColorValue($key, $value);

            return;
        }

        if (self::isDurationKey($key)) {
            self::assertDurationValue($key, $value);

            return;
        }

        if (self::isOpacityKey($key)) {
            self::assertOpacityValue($key, $value);

            return;
        }

        if (self::isLengthKey($key)) {
            self::assertLengthValue($key, $value);

            return;
        }

        if (self::isEasingKey($key)) {
            self::assertEasingValue($key, $value);
        }
    }

    private static function isBlurKey(string $key): bool
    {
        return str_contains($key, 'blur');
    }

    private static function isColorKey(string $key): bool
    {
        return (str_contains($key, 'color')
            || str_contains($key, 'shadow')
            || str_contains($key, 'backdrop')
            || str_contains($key, 'overlay'))
            && !self::isBlurKey($key);
    }

    private static function isDurationKey(string $key): bool
    {
        return str_contains($key, 'duration') || str_contains($key, 'transition');
    }

    private static function isLengthKey(string $key): bool
    {
        return (str_contains($key, 'space')
            || str_contains($key, 'radius')
            || str_contains($key, 'ring')
            || str_contains($key, 'gap')
            || str_contains($key, 'size')
            || str_contains($key, 'line-height')
            || str_contains($key, 'weight'))
            && !self::isBlurKey($key)
            && !self::isOpacityKey($key);
    }

    private static function isOpacityKey(string $key): bool
    {
        return str_contains($key, 'opacity');
    }

    private static function isEasingKey(string $key): bool
    {
        return str_contains($key, 'easing');
    }

    private static function assertColorValue(string $key, string $value): void
    {
        if (preg_match('/^#[0-9a-fA-F]{3,8}$/', $value) === 1) {
            return;
        }

        if (preg_match('/^rgba?\(\s*\d+\s*,/', $value) === 1) {
            return;
        }

        if (preg_match('/^color-mix\(/', $value) === 1) {
            return;
        }

        if (preg_match('/^(transparent|currentColor)$/', $value) === 1) {
            return;
        }

        if (preg_match('/^linear-gradient\(/', $value) === 1 && str_contains($key, 'skeleton')) {
            return;
        }

        if (preg_match('/^0\s+\d+px\s+\d+px\s+rgba\(/', $value) === 1 && str_contains($key, 'shadow')) {
            return;
        }

        if (str_contains($key, 'shadow')
            && str_contains($value, 'inset')
            && str_contains($value, 'color-mix(in oklch')) {
            return;
        }

        if (self::isAllowedOklchColor($value)) {
            return;
        }

        ThemeErrorCatalog::throw(
            ThemeErrorCatalog::INVALID_TOKEN_VALUE,
            sprintf('Token "%s" value is not an allowed color form.', $key),
        );
    }

    private static function isAllowedOklchColor(string $value): bool
    {
        if (!str_starts_with($value, 'oklch(') || !str_ends_with($value, ')')) {
            return false;
        }

        if (preg_match('/^oklch\(from var\(--ui-color-[\w-]+\)\s+calc\(l \* [\d.]+\)\s+c\s+h\)$/', $value) === 1) {
            return true;
        }

        return preg_match(
            '/^oklch\(\s*[\d.]+\s+[\d.]+\s+[\d.]+(?:\s*\/\s*[\d.]+)?\s*\)$/',
            $value,
        ) === 1;
    }

    private static function assertDurationValue(string $key, string $value): void
    {
        if (preg_match('/^\d+(\.\d+)?(ms|s)$/', $value) !== 1) {
            ThemeErrorCatalog::throw(
                ThemeErrorCatalog::INVALID_TOKEN_VALUE,
                sprintf('Token "%s" must be a duration (ms|s).', $key),
            );
        }
    }

    private static function assertLengthValue(string $key, string $value): void
    {
        if ($value === '0') {
            return;
        }

        if (preg_match('/^\d+(\.\d+)?(rem|em|px|%|ch)$/', $value) === 1) {
            return;
        }

        if (preg_match('/^9999px$/', $value) === 1 && str_contains($key, 'full')) {
            return;
        }

        if (preg_match('/^"\s*.+"\s*,/', $value) === 1 && str_contains($key, 'font-family')) {
            return;
        }

        if (preg_match('/^\d+(\.\d+)?$/', $value) === 1 && str_contains($key, 'weight')) {
            return;
        }

        if (preg_match('/^\d+(\.\d+)?$/', $value) === 1 && str_contains($key, 'line-height')) {
            return;
        }

        ThemeErrorCatalog::throw(
            ThemeErrorCatalog::INVALID_TOKEN_VALUE,
            sprintf('Token "%s" must be a length or numeric weight/line-height.', $key),
        );
    }

    private static function assertOpacityValue(string $key, string $value): void
    {
        if (preg_match('/^0(\.\d+)?|1(\.0)?$/', $value) !== 1) {
            ThemeErrorCatalog::throw(
                ThemeErrorCatalog::INVALID_TOKEN_VALUE,
                sprintf('Token "%s" opacity must be 0–1.', $key),
            );
        }
    }

    private static function assertEasingValue(string $key, string $value): void
    {
        if ($value === 'linear') {
            return;
        }

        if (preg_match('/^cubic-bezier\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+\s*\)$/', $value) === 1) {
            return;
        }

        ThemeErrorCatalog::throw(
            ThemeErrorCatalog::INVALID_TOKEN_VALUE,
            sprintf('Token "%s" must be linear or cubic-bezier(...).', $key),
        );
    }
}
