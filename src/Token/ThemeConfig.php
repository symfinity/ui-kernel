<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use Symfinity\UiKernel\Dtcg\BuiltinDtcgThemeCatalog;
use Symfinity\UiKernel\Dtcg\BuiltinThemeVariant;
use Symfinity\UiKernel\Theme\LayoutProfile;

/**
 * Built-in theme definitions — palette recipe access for tests and palette tooling.
 *
 * Runtime theme resolution uses {@see \Symfinity\UiKernel\Dtcg\ThemeDtcgResolver} (077).
 */
final class ThemeConfig
{
    public function __construct(
        private readonly string $id,
        private readonly string $label,
        private readonly LayoutProfile $layout,
        private readonly MonoTone $tone,
        private readonly ThemePaletteRecipe $paletteRecipe,
        private readonly string $layerPath,
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

    public function scrollMotion(): bool
    {
        return $this->scrollMotion;
    }

    public function backdropBlur(): string
    {
        return $this->backdropBlur;
    }

    /**
     * @return array<string, string> semantic role => palette ref
     */
    public function colorRefs(): array
    {
        return (new \Symfinity\UiKernel\Dtcg\DtcgLayerReader())->colorRefsFromLayer($this->layerPath);
    }

    /**
     * @return array<string, string> short appearance token keys => CSS value
     */
    public function appearanceTokens(): array
    {
        $document = (new \Symfinity\UiKernel\Dtcg\DtcgYamlReader())->read($this->layerPath);
        $short = [];
        foreach ($document->flatten() as $path => $token) {
            if (str_starts_with($path, 'color.')) {
                continue;
            }
            $cssKey = '--ui-' . str_replace('.', '-', $path);
            $value = $token->value();
            if (\is_string($value)) {
                $short[\Symfinity\UiKernel\Token\ThemeTokenMap::cssVarToShortKey($cssKey)] = $value;
            }
        }

        return $short;
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
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        return array_map(
            static fn (BuiltinThemeVariant $variant): self => self::fromVariant($variant),
            self::catalog()->all(),
        );
    }

    public static function get(string $id): self
    {
        return self::fromVariant(self::catalog()->get($id));
    }

    public static function fromVariant(BuiltinThemeVariant $variant): self
    {
        return new self(
            $variant->id(),
            $variant->label(),
            $variant->layout(),
            $variant->tone(),
            $variant->paletteRecipe(),
            $variant->layerPath(),
            $variant->schemaVersion(),
            $variant->scrollMotion(),
            $variant->backdropBlur(),
        );
    }

    private static function catalog(): BuiltinDtcgThemeCatalog
    {
        return new BuiltinDtcgThemeCatalog(BuiltinDtcgThemeCatalog::defaultDirectory());
    }
}
