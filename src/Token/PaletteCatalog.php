<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use Symfony\Component\Yaml\Yaml;

final class PaletteCatalog
{
    /** @var array<string, mixed>|null */
    private static ?array $config = null;

    /**
     * @return list<int>
     */
    public static function levels(): array
    {
        /** @var list<int> $levels */
        $levels = self::config()['palette_contract']['levels'];

        return $levels;
    }

    /**
     * @return list<int>
     */
    public static function alphaPercent(): array
    {
        /** @var list<int> $alpha */
        $alpha = self::config()['palette_contract']['alpha_percent'];

        return $alpha;
    }

    /**
     * @return list<string>
     */
    public static function hueFamilies(): array
    {
        /** @var list<string> $hues */
        $hues = self::config()['palette_contract']['hue_families'];

        return $hues;
    }

    /**
     * @return list<string>
     */
    public static function hues(): array
    {
        /** @var list<string> $hues */
        $hues = self::config()['palette_contract']['hues'];

        return $hues;
    }

    /**
     * @return list<string>
     */
    public static function forbiddenHues(): array
    {
        /** @var list<string> $hues */
        $hues = self::config()['palette_contract']['forbidden_hues'];

        return $hues;
    }

    /**
     * @return list<string>
     */
    public static function monoTones(): array
    {
        /** @var list<string> $spices */
        $spices = self::config()['palette_contract']['mono_tones'];

        return $spices;
    }

    /**
     * @return list<int>
     */
    public static function rampLevels(): array
    {
        /** @var list<int> $levels */
        $levels = self::config()['palette_generator']['ramp_levels'];

        return $levels;
    }

    /**
     * @return array<int, float>
     */
    public static function levelLightness(): array
    {
        /** @var array<int, float> $levels */
        $levels = self::config()['palette_generator']['level_lightness'];

        return $levels;
    }

    /**
     * @return array<int, float>
     */
    public static function levelLightnessPure(): array
    {
        /** @var array<int, float> $levels */
        $levels = self::config()['palette_generator']['level_lightness_pure'];

        return $levels;
    }

    /**
     * @return array<string, array{extends?: string, hue_base?: array<string, float>, mono_tones?: array<string, array{hue: float, saturation: float}>, hue_overrides?: array<string, float>, mono_overrides?: array<string, array{hue?: float, saturation?: float}>}>
     */
    public static function presets(): array
    {
        /** @var array<string, array{extends?: string, hue_base?: array<string, float>, mono_tones?: array<string, array{hue: float, saturation: float}>, hue_overrides?: array<string, float>, mono_overrides?: array<string, array{hue?: float, saturation?: float}>}> $lineages */
        $lineages = self::config()['presets'];

        return $lineages;
    }

    /**
     * @return list<array{id: string, label: string, layout: string, tone: string, preset: string, colors: array<string, string>, scroll_motion?: bool, backdrop_blur?: string}>
     */
    public static function themes(): array
    {
        /** @var list<array{id: string, label: string, layout: string, tone: string, preset: string, colors: array<string, string>, scroll_motion?: bool, backdrop_blur?: string}> $themes */
        $themes = self::config()['themes'];

        return $themes;
    }

    /**
     * @return array<string, mixed>
     */
    private static function config(): array
    {
        if (self::$config !== null) {
            return self::$config;
        }

        $configPath = dirname(__DIR__, 2) . '/config/palette_ssot.yaml';
        /** @var array<string, mixed> $parsed */
        $parsed = Yaml::parseFile($configPath);
        self::$config = $parsed;

        return self::$config;
    }
}
