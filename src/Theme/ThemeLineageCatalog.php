<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

use InvalidArgumentException;
use Symfinity\UiKernel\Token\BuiltinThemeCatalog;

/**
 * Maps lineage keys to light/dark public theme ids (registry SSOT).
 */
final class ThemeLineageCatalog
{
    /** @var array<string, array{light: string, dark: string}>|null */
    private static ?array $pairs = null;

    /**
     * @return array{light: string, dark: string}
     */
    public static function pairForLineage(string $lineage): array
    {
        $pairs = self::pairs();
        if (!isset($pairs[$lineage])) {
            throw new InvalidArgumentException(sprintf('Unknown theme lineage "%s".', $lineage));
        }

        return $pairs[$lineage];
    }

    /**
     * @return list<string>
     */
    public static function lineages(): array
    {
        return array_keys(self::pairs());
    }

    public static function lineageForThemeId(string $themeId): string
    {
        foreach (BuiltinThemeCatalog::themes() as $theme) {
            if ($theme['id'] === $themeId) {
                $lineage = $theme['lineage'] ?? null;
                if (!is_string($lineage) || $lineage === '') {
                    break;
                }

                return $lineage;
            }
        }

        throw new InvalidArgumentException(sprintf('Unknown theme id "%s".', $themeId));
    }

    public static function isDarkThemeId(string $themeId): bool
    {
        foreach (self::pairs() as $pair) {
            if ($pair['dark'] === $themeId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, array{light: string, dark: string}>
     */
    public static function pairs(): array
    {
        if (self::$pairs !== null) {
            return self::$pairs;
        }

        /** @var array<string, array{light?: string, dark?: string}> $building */
        $building = [];

        foreach (BuiltinThemeCatalog::themes() as $theme) {
            $lineage = $theme['lineage'] ?? null;
            if (!is_string($lineage) || $lineage === '') {
                continue;
            }

            $id = $theme['id'];
            $slot = str_ends_with($id, '-dark') ? 'dark' : 'light';
            $building[$lineage][$slot] = $id;
        }

        foreach ($building as $lineage => $pair) {
            if (!isset($pair['light'], $pair['dark'])) {
                throw new \RuntimeException(sprintf(
                    'Built-in lineage "%s" must define light and dark variants.',
                    (string) $lineage,
                ));
            }
        }

        /** @var array<string, array{light: string, dark: string}> $pairs */
        $pairs = $building;
        self::$pairs = $pairs;

        return self::$pairs;
    }

    public static function reset(): void
    {
        self::$pairs = null;
    }
}
