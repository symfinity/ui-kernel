<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

final class ThemeTokenSchema
{
    public const V1_0 = '1.0';

    public const V2_0 = '2.0';

    /** @var list<string> */
    public const COLOR_KEYS_V1 = [
        '--ui-color-primary',
        '--ui-color-secondary',
        '--ui-color-surface',
        '--ui-color-surface-elevated',
        '--ui-color-text',
        '--ui-color-text-muted',
        '--ui-color-border',
        '--ui-color-danger',
        '--ui-color-success',
    ];

    /** @var list<string> */
    public const LAYOUT_KEYS_V1 = [
        '--ui-space-xs',
        '--ui-space-sm',
        '--ui-space-md',
        '--ui-space-lg',
        '--ui-space-xl',
        '--ui-radius-sm',
        '--ui-radius-md',
        '--ui-radius-lg',
        '--ui-font-family-sans',
        '--ui-font-size-sm',
        '--ui-font-size-md',
        '--ui-font-size-lg',
        '--ui-transition-duration',
    ];

    /** @var list<string> */
    public const COLOR_KEYS_V2_ADDITIVE = [
        '--ui-color-tertiary',
        '--ui-color-warning',
        '--ui-color-info',
        '--ui-color-focus',
        '--ui-color-overlay',
        '--ui-color-skeleton-base',
        '--ui-color-skeleton-shine',
    ];

    /** @var list<string> */
    public const OVERLAY_KEYS_V2_ADDITIVE = [
        '--ui-overlay-surface',
        '--ui-overlay-border',
        '--ui-overlay-shadow',
        '--ui-backdrop-color',
        '--ui-backdrop-blur',
    ];

    /** @var list<string> */
    public const LAYOUT_KEYS_V2_ADDITIVE = [
        '--ui-space-2xl',
        '--ui-grid-gap',
        '--ui-radius-xs',
        '--ui-radius-full',
        '--ui-shadow-sm',
        '--ui-shadow-md',
        '--ui-shadow-lg',
        '--ui-font-family-mono',
        '--ui-font-weight-normal',
        '--ui-font-weight-medium',
        '--ui-font-weight-semibold',
        '--ui-line-height-tight',
        '--ui-line-height-normal',
        '--ui-line-height-relaxed',
        '--ui-motion-duration-fast',
        '--ui-motion-duration-normal',
        '--ui-motion-duration-slow',
        '--ui-motion-duration-skeleton',
        '--ui-motion-easing-standard',
        '--ui-motion-easing-linear',
        '--ui-focus-ring-width',
        '--ui-focus-ring-opacity',
        '--ui-focus-ring-blur',
    ];

    /** @var list<string> */
    public const COLOR_KEYS = [
        ...self::COLOR_KEYS_V1,
        ...self::COLOR_KEYS_V2_ADDITIVE,
    ];

    /** @var list<string> */
    public const LAYOUT_KEYS = [
        ...self::LAYOUT_KEYS_V1,
        ...self::LAYOUT_KEYS_V2_ADDITIVE,
    ];

    /** @var list<string> */
    public const REQUIRED_KEYS_V1 = [
        ...self::COLOR_KEYS_V1,
        ...self::LAYOUT_KEYS_V1,
    ];

    /** @var list<string> */
    public const REQUIRED_KEYS_V2 = [
        ...self::REQUIRED_KEYS_V1,
        ...self::COLOR_KEYS_V2_ADDITIVE,
        ...self::LAYOUT_KEYS_V2_ADDITIVE,
        ...self::OVERLAY_KEYS_V2_ADDITIVE,
    ];

    /** @var list<string> */
    public const REQUIRED_KEYS = self::REQUIRED_KEYS_V1;

    /**
     * @return list<string>
     */
    public static function requiredKeys(string $schemaVersion = self::V1_0): array
    {
        return $schemaVersion === self::V2_0 ? self::REQUIRED_KEYS_V2 : self::REQUIRED_KEYS_V1;
    }
}
