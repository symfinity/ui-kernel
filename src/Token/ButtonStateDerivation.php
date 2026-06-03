<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Centralized button hover/active derivation from semantic base colours (018 FR-011).
 */
final class ButtonStateDerivation
{
    public const ALGORITHM_VERSION = '1.0';

    /** Percent black mixed into base for hover emphasis. */
    private const HOVER_BLACK_MIX_PERCENT = 12;

    /** Percent black mixed into base for active — must exceed hover. */
    private const ACTIVE_BLACK_MIX_PERCENT = 24;

    public static function hoverFromHex(string $baseHex): string
    {
        return self::mixBlack($baseHex, self::HOVER_BLACK_MIX_PERCENT);
    }

    public static function activeFromHex(string $baseHex): string
    {
        return self::mixBlack($baseHex, self::ACTIVE_BLACK_MIX_PERCENT);
    }

    public static function isActiveStrongerThanHover(string $baseHex): bool
    {
        $hover = self::hoverFromHex($baseHex);
        $active = self::activeFromHex($baseHex);

        return self::relativeLuminance($active) < self::relativeLuminance($hover);
    }

    public static function cssHoverBackground(string $tokenVar): string
    {
        return sprintf(
            'color-mix(in srgb, var(%s) %d%%, black)',
            $tokenVar,
            100 - self::HOVER_BLACK_MIX_PERCENT,
        );
    }

    public static function cssActiveBackground(string $tokenVar): string
    {
        return sprintf(
            'color-mix(in srgb, var(%s) %d%%, black)',
            $tokenVar,
            100 - self::ACTIVE_BLACK_MIX_PERCENT,
        );
    }

    /**
     * Selector guard suppressing pointer-driven states for disabled/loading (FR-013).
     */
    public static function interactionGuard(): string
    {
        return ':not([disabled]):not([aria-disabled="true"]):not([data-ui-state="disabled"]):not([data-ui-state="loading"])';
    }

    private static function mixBlack(string $hex, int $blackPercent): string
    {
        [$r, $g, $b] = self::hexToRgb($hex);
        $factor = (100 - $blackPercent) / 100;

        return sprintf(
            '#%02x%02x%02x',
            (int) round($r * $factor),
            (int) round($g * $factor),
            (int) round($b * $factor),
        );
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    private static function relativeLuminance(string $hex): float
    {
        [$r, $g, $b] = self::hexToRgb($hex);
        $channels = [$r / 255, $g / 255, $b / 255];
        $linear = array_map(
            static fn (float $c): float => $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4,
            $channels,
        );

        return 0.2126 * $linear[0] + 0.7152 * $linear[1] + 0.0722 * $linear[2];
    }
}
