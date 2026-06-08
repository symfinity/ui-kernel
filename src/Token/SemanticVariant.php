<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Closed enum for Chameleon semantic colour on {@code data-ui-variant} (060).
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
     * Map legacy showcase / shadcn alias values to canonical semantic colour (060 migration shim).
     */
    public static function coerceLegacy(string $value): string
    {
        return match ($value) {
            'default', '' => 'primary',
            'destructive' => 'danger',
            default => $value,
        };
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

            $coerced = self::coerceLegacy((string) $normalized[$name]);
            $normalized[$name] = self::isValid($coerced) ? $coerced : 'primary';
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
