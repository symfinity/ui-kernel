<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use Symfinity\UiKernel\Theme\LayoutProfile;

/**
 * Built-in theme definitions — palette refs only (no hex).
 */
final class ThemeConfig
{
    /**
     * @param array<string, string> $colorRefs semantic role => palette ref
     */
    public function __construct(
        private readonly string $id,
        private readonly string $label,
        private readonly LayoutProfile $layout,
        private readonly MonoTone $tone,
        private readonly ThemePaletteRecipe $paletteRecipe,
        private readonly array $colorRefs,
        private readonly string $schemaVersion = ThemeTokenSchema::V2_0,
        private readonly bool $scrollMotion = false,
        private readonly string $backdropBlur = '0',
    ) {
    }

    public function paletteRecipe(): ThemePaletteRecipe
    {
        return $this->paletteRecipe;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function layout(): LayoutProfile
    {
        return $this->layout;
    }

    public function tone(): MonoTone
    {
        return $this->tone;
    }

    public function schemaVersion(): string
    {
        return $this->schemaVersion;
    }

    /**
     * @return array<string, string>
     */
    public function colorRefs(): array
    {
        return $this->colorRefs;
    }

    public function scrollMotion(): bool
    {
        return $this->scrollMotion;
    }

    public function backdropBlur(): string
    {
        return $this->backdropBlur;
    }

    public function presetHash(): string
    {
        $recipe = $this->paletteRecipe;

        return hash('sha256', json_encode([
            'layout' => $this->layout->value,
            'tone' => $this->tone->value,
            'hueBase' => $recipe->hueBase(),
            'monoTones' => $recipe->monoTones(),
            'colorRefs' => $this->colorRefs,
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        return array_map(
            static fn (array $definition): self => new self(
                $definition['id'],
                $definition['label'],
                $definition['layout'],
                $definition['tone'],
                $definition['paletteRecipe'],
                $definition['colors'],
                $definition['schemaVersion'] ?? ThemeTokenSchema::V2_0,
                $definition['scrollMotion'] ?? false,
                $definition['backdropBlur'] ?? '0',
            ),
            self::definitions(),
        );
    }

    public static function get(string $id): self
    {
        foreach (self::all() as $config) {
            if ($config->id() === $id) {
                return $config;
            }
        }

        throw new \InvalidArgumentException(sprintf('Unknown theme id "%s".', $id));
    }

    /**
     * @return list<array{id: string, label: string, layout: LayoutProfile, tone: MonoTone, paletteRecipe: ThemePaletteRecipe, colors: array<string, string>, schemaVersion?: string, scrollMotion?: bool, backdropBlur?: string}>
     */
    private static function definitions(): array
    {
        $presets = PaletteCatalog::presets();
        $recipes = [];
        $definitions = [];

        foreach (PaletteCatalog::themes() as $theme) {
            $preset = $theme['preset'];
            if (!isset($recipes[$preset])) {
                $recipes[$preset] = self::recipeForPreset($preset, $presets, $recipes);
            }

            $definitions[] = [
                'id' => $theme['id'],
                'label' => $theme['label'],
                'layout' => self::layoutProfile($theme['layout']),
                'tone' => MonoTone::from($theme['tone']),
                'paletteRecipe' => $recipes[$preset],
                'colors' => $theme['colors'],
                'schemaVersion' => ThemeTokenSchema::V2_0,
                'scrollMotion' => $theme['scroll_motion'] ?? false,
                'backdropBlur' => $theme['backdrop_blur'] ?? '0',
            ];
        }

        return $definitions;
    }

    /**
     * @param array<string, array{extends?: string, hue_base?: array<string, float>, mono_tones?: array<string, array{hue: float, saturation: float}>, hue_overrides?: array<string, float>, mono_overrides?: array<string, array{hue?: float, saturation?: float}>}> $presets
     * @param array<string, ThemePaletteRecipe> $resolved
     */
    private static function recipeForPreset(string $preset, array $presets, array &$resolved): ThemePaletteRecipe
    {
        if (isset($resolved[$preset])) {
            return $resolved[$preset];
        }

        $definition = $presets[$preset] ?? null;
        if ($definition === null) {
            throw new \InvalidArgumentException(sprintf('Unknown preset "%s".', $preset));
        }

        if (isset($definition['hue_base'], $definition['mono_tones'])) {
            $resolved[$preset] = new ThemePaletteRecipe($definition['hue_base'], $definition['mono_tones']);

            return $resolved[$preset];
        }

        $parent = $definition['extends'] ?? null;
        if (!is_string($parent) || $parent === '') {
            throw new \InvalidArgumentException(sprintf('Preset "%s" must define extends or baseline values.', $preset));
        }

        $parentRecipe = self::recipeForPreset($parent, $presets, $resolved);
        $hueBase = $parentRecipe->hueBase();
        foreach (($definition['hue_overrides'] ?? []) as $hue => $degrees) {
            $hueBase[$hue] = $degrees;
        }

        $monoTones = $parentRecipe->monoTones();
        foreach (($definition['mono_overrides'] ?? []) as $tone => $params) {
            $monoTones[$tone] = [
                'hue' => $params['hue'] ?? $monoTones[$tone]['hue'],
                'saturation' => $params['saturation'] ?? $monoTones[$tone]['saturation'],
            ];
        }

        $resolved[$preset] = new ThemePaletteRecipe($hueBase, $monoTones);

        return $resolved[$preset];
    }

    private static function layoutProfile(string $value): LayoutProfile
    {
        return match ($value) {
            'Kiroshi' => LayoutProfile::Kiroshi,
            'Semantic' => LayoutProfile::Semantic,
            'Utility' => LayoutProfile::Utility,
            default => throw new \InvalidArgumentException(sprintf('Unknown layout profile "%s".', $value)),
        };
    }
}
