<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

final class ThemeTokenSchema
{
    /** @var list<string> */
    public const COLOR_KEYS = [
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
    public const LAYOUT_KEYS = [
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
    public const REQUIRED_KEYS = [
        ...self::COLOR_KEYS,
        ...self::LAYOUT_KEYS,
    ];
}
