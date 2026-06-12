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
     * @param array<string, string>              $colorRefs        semantic role => palette ref
     * @param array<string, string>              $appearanceTokens CSS var => value (from YAML tokens)
     */
    public function __construct(
        private readonly string $id,
        private readonly string $label,
        private readonly LayoutProfile $layout,
        private readonly MonoTone $tone,
        private readonly ThemePaletteRecipe $paletteRecipe,
        private readonly array $colorRefs,
        private readonly array $appearanceTokens,
        private readonly string $schemaVersion = ThemeTokenSchema::V1_0,
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

    /**
     * @return array<string, string>
     */
    public function appearanceTokens(): array
    {
        return $this->appearanceTokens;
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
            'layout' => $this->layout->name,
            'tone' => $this->tone->value,
            'hueBase' => $recipe->hueBase(),
            'hueChroma' => $recipe->hueChromaOverrides(),
            'scaleAnchors' => $recipe->scaleAnchors(),
            'monoTones' => $recipe->monoTones(),
            'colorRefs' => $this->colorRefs,
            'appearanceTokens' => $this->appearanceTokens,
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
                $definition['appearanceTokens'],
                $definition['schemaVersion'] ?? ThemeTokenSchema::V1_0,
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
        $definitions = [];

        foreach (BuiltinThemeCatalog::themes() as $theme) {
            $definitions[] = [
                'id' => $theme['id'],
                'label' => $theme['label'],
                'layout' => self::layoutProfile($theme['layout']),
                'tone' => MonoTone::from($theme['tone']),
                'paletteRecipe' => self::recipeFromTheme($theme),
                'colors' => $theme['colors'],
                'appearanceTokens' => ThemeTokenMap::toCssVariables($theme['tokens']),
                'schemaVersion' => ThemeTokenSchema::V1_0,
                'scrollMotion' => $theme['scroll_motion'] ?? false,
                'backdropBlur' => $theme['backdrop_blur'] ?? '0',
            ];
        }

        return $definitions;
    }

    /**
     * @param array{hue_base: array<string, float>, mono_tones: array<string, array{hue: float, saturation: float}>} $theme
     */
    private static function recipeFromTheme(array $theme): ThemePaletteRecipe
    {
        return ThemePaletteRecipe::fromPaletteDefinition(
            $theme['hue_base'],
            $theme['mono_tones'],
            $theme['hue_chroma'] ?? [],
            $theme['scale_anchors'] ?? [],
        );
    }

    private static function layoutProfile(string $value): LayoutProfile
    {
        return match ($value) {
            'Semantic' => LayoutProfile::Semantic,
            'Utility' => LayoutProfile::Utility,
            default => throw new \InvalidArgumentException(sprintf('Unknown layout profile "%s".', $value)),
        };
    }
}
