<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use Symfony\Component\Yaml\Yaml;

/**
 * Built-in UI Kernel themes — {@see symfinity_ui_kernel.themes} in config/themes/*.yaml (schema 1.0).
 */
final class BuiltinThemeCatalog
{
    /**
     * @var list<array{
     *     id: string,
     *     label: string,
     *     layout: string,
     *     tone: string,
     *     colors: array<string, string>,
     *     hue_base: array<string, float>,
     *     mono_tones: array<string, array{hue: float, saturation: float}>,
     *     tokens: array<string, string>,
     *     lineage?: string,
     *     scroll_motion?: bool,
     *     backdrop_blur?: string
     * }>|null
     */
    private static ?array $themes = null;

    /** @var array<string, string>|null */
    private static ?array $lineageDonors = null;

    /**
     * @return list<array{
     *     id: string,
     *     label: string,
     *     layout: string,
     *     tone: string,
     *     colors: array<string, string>,
     *     hue_base: array<string, float>,
     *     mono_tones: array<string, array{hue: float, saturation: float}>,
     *     tokens: array<string, string>,
     *     lineage?: string,
     *     scroll_motion?: bool,
     *     backdrop_blur?: string
     * }>
     */
    public static function themes(): array
    {
        if (self::$themes !== null) {
            return self::$themes;
        }

        self::load();

        return self::$themes;
    }

    /**
     * @return array<string, string>
     */
    public static function lineageDonors(): array
    {
        if (self::$lineageDonors !== null) {
            return self::$lineageDonors;
        }

        self::load();

        return self::$lineageDonors;
    }

    public static function reset(): void
    {
        self::$themes = null;
        self::$lineageDonors = null;
    }

    private static function load(): void
    {
        $directory = dirname(__DIR__, 2) . '/config/themes';
        if (!is_dir($directory)) {
            throw new \RuntimeException(sprintf('Built-in theme directory "%s" is missing.', $directory));
        }

        $paths = glob($directory . '/*.yaml') ?: [];
        sort($paths);

        $themes = [];
        $lineageDonors = [];

        foreach ($paths as $path) {
            $basename = basename($path, '.yaml');
            if ($basename === 'README' || str_starts_with($basename, '_')) {
                continue;
            }

            /** @var mixed $parsed */
            $parsed = Yaml::parseFile($path);
            if (!is_array($parsed)) {
                throw new \InvalidArgumentException(sprintf('Theme file "%s" must be a YAML mapping.', $path));
            }

            /** @var array<string, mixed> $kernel */
            $kernel = $parsed['symfinity_ui_kernel'] ?? $parsed;
            $themeMap = $kernel['themes'] ?? null;
            if (!is_array($themeMap) || $themeMap === []) {
                throw new \InvalidArgumentException(sprintf(
                    'Theme file "%s" must define symfinity_ui_kernel.themes with at least one entry (schema 1.0).',
                    $path,
                ));
            }

            foreach ($themeMap as $lineageKey => $group) {
                if (!is_string($lineageKey) || !is_array($group)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Theme file "%s": symfinity_ui_kernel.themes.%s must be a mapping.',
                        $path,
                        is_string($lineageKey) ? $lineageKey : (string) $lineageKey,
                    ));
                }

                foreach (ThemeYamlNormalizer::expandVariants($group, $lineageKey, $path) as $theme) {
                    $context = sprintf('%s themes.%s variant %s', $path, $lineageKey, $theme['id']);
                    ThemeTokenMap::assertComplete($theme['tokens'], ThemeTokenSchema::V1_0, $context);

                    $lineage = $theme['lineage'] ?? $lineageKey;
                    if (!isset($lineageDonors[$lineage])) {
                        $lineageDonors[$lineage] = $theme['id'];
                    }

                    $themes[] = $theme;
                }
            }
        }

        if ($themes === []) {
            throw new \RuntimeException(sprintf('No built-in themes found in "%s".', $directory));
        }

        self::$themes = $themes;
        self::$lineageDonors = $lineageDonors;
    }
}
