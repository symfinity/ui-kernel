<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Maps interactive button variants to canonical semantic colour tokens (018).
 */
final class ButtonVariantMap
{
    /** @var list<string> */
    public const SEMANTIC_VARIANTS = [
        'primary',
        'secondary',
        'tertiary',
        'danger',
        'success',
        'warning',
        'info',
    ];

    public static function semanticTokenKey(string $variant): string
    {
        return match ($variant) {
            'primary', 'default' => '--ui-color-primary',
            'secondary' => '--ui-color-secondary',
            'tertiary' => '--ui-color-tertiary',
            'danger', 'destructive' => '--ui-color-danger',
            'success' => '--ui-color-success',
            'warning' => '--ui-color-warning',
            'info' => '--ui-color-info',
            default => ThemeErrorCatalog::throw(
                ThemeErrorCatalog::UNKNOWN_TOKEN_KEY,
                sprintf('Unknown button variant "%s".', $variant),
            ),
        };
    }

    /**
     * @return array<string, list<string>> canonical semantic variant => data-ui-variant attribute values
     */
    public static function cssVariantSelectors(): array
    {
        return [
            'primary' => ['default', 'primary'],
            'secondary' => ['secondary'],
            'tertiary' => ['tertiary'],
            'danger' => ['destructive', 'danger'],
            'success' => ['success'],
            'warning' => ['warning'],
            'info' => ['info'],
        ];
    }
}
