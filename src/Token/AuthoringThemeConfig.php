<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use Symfinity\UiKernel\Theme\LayoutProfile;
use Symfinity\UiKernel\Theme\PhysicsId;

/**
 * Bespoke theme definition for ui-themer / consumer YAML — not built-in DTCG catalog (077 boundary).
 */
final class AuthoringThemeConfig
{
    /**
     * @param array<string, string> $colorRefs
     * @param array<string, string> $appearanceTokens
     */
    public function __construct(
        private readonly string $id,
        private readonly string $label,
        private readonly LayoutProfile $layout,
        private readonly MonoTone $tone,
        private readonly ThemePaletteRecipe $paletteRecipe,
        private readonly array $colorRefs,
        private readonly array $appearanceTokens,
        private readonly string $schemaVersion = ThemeTokenSchema::V2_0,
        private readonly bool $scrollMotion = false,
        private readonly string $backdropBlur = '0',
        private readonly PhysicsId $physics = PhysicsId::Flat,
    ) {
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

    public function paletteRecipe(): ThemePaletteRecipe
    {
        return $this->paletteRecipe;
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

    public function schemaVersion(): string
    {
        return $this->schemaVersion;
    }

    public function scrollMotion(): bool
    {
        return $this->scrollMotion;
    }

    public function backdropBlur(): string
    {
        return $this->backdropBlur;
    }

    public function physics(): PhysicsId
    {
        return $this->physics;
    }
}
