<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Closed enum for semantic colour on {@code data-ui-variant}.
 */
final class SemanticVariant
{
    /** @var list<string> */
    public const ALL = [
        'primary',
        'secondary',
        'tertiary',
        'success',
        'danger',
        'info',
        'warning',
        'ghost',
    ];

    public static function isValid(string $value): bool
    {
        return \in_array($value, self::ALL, true);
    }

    /**
     * @param array<string, mixed> $props
     *
     * @return array<string, mixed>
     */
    public static function normalizeColourProps(array $props, string ...$propNames): array
    {
        $normalized = $props;

        foreach ($propNames as $name) {
            if (!\array_key_exists($name, $normalized) || !\is_scalar($normalized[$name])) {
                continue;
            }

            $value = (string) $normalized[$name];
            $normalized[$name] = self::isValid($value) ? $value : 'primary';
        }

        return $normalized;
    }

    public static function tokenKey(string $variant): string
    {
        if (!self::isValid($variant)) {
            ThemeErrorCatalog::throw(
                ThemeErrorCatalog::UNKNOWN_TOKEN_KEY,
                sprintf('Unknown semantic variant "%s".', $variant),
            );
        }

        return match ($variant) {
            'primary' => '--ui-color-primary',
            'secondary' => '--ui-color-secondary',
            'tertiary' => '--ui-color-tertiary',
            'danger' => '--ui-color-danger',
            'success' => '--ui-color-success',
            'warning' => '--ui-color-warning',
            'info' => '--ui-color-info',
            'ghost' => '--ui-color-text-muted',
        };
    }

    /**
     * @return array<string, string> variant => CSS custom property
     */
    public static function tokenMap(): array
    {
        $map = [];
        foreach (self::ALL as $variant) {
            $map[$variant] = self::tokenKey($variant);
        }

        return $map;
    }
}
