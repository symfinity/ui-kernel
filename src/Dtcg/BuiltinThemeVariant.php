<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Dtcg;

use Symfinity\UiKernel\Theme\LayoutProfile;
use Symfinity\UiKernel\Token\MonoTone;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

/**
 * One registered built-in theme variant loaded from DTCG on-disk layout (077).
 */
final readonly class BuiltinThemeVariant
{
    /**
     * @param array<string, mixed> $paletteDefinition raw palette block from theme.meta.yaml
     */
    public function __construct(
        private string $id,
        private string $label,
        private string $lineage,
        private string $designSystemId,
        private LayoutProfile $layout,
        private MonoTone $tone,
        private string $layerPath,
        private array $paletteDefinition,
        private string $schemaVersion = '1.0',
        private bool $scrollMotion = false,
        private string $backdropBlur = '0',
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

    public function lineage(): string
    {
        return $this->lineage;
    }

    public function designSystemId(): string
    {
        return $this->designSystemId;
    }

    public function layout(): LayoutProfile
    {
        return $this->layout;
    }

    public function tone(): MonoTone
    {
        return $this->tone;
    }

    public function layerPath(): string
    {
        return $this->layerPath;
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

    public function paletteRecipe(): ThemePaletteRecipe
    {
        return ThemePaletteRecipe::fromPaletteDefinition(
            $this->paletteDefinition['hue_base'],
            $this->paletteDefinition['mono_tones'],
            $this->paletteDefinition['hue_chroma'] ?? [],
            $this->paletteDefinition['scale_anchors'] ?? [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function paletteDefinition(): array
    {
        return $this->paletteDefinition;
    }
}
