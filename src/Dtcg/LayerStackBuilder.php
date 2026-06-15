<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Dtcg;

use Symfinity\UiKernel\Contract\Layer\LayerRole;
use Symfinity\UiKernel\Contract\Layer\LayerStack;
use Symfinity\UiKernel\Contract\Layer\TokenLayerInterface;
use Symfinity\UiKernel\Contract\Layer\TokenLayer;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

/**
 * Assembles base ⊕ design_system ⊕ theme layers for a built-in variant (077).
 */
final class LayerStackBuilder
{
    public function __construct(
        private readonly DesignSystemLayerRegistry $designSystems,
        private readonly PaletteGenerator $paletteGenerator = new PaletteGenerator(),
        private readonly DtcgYamlReader $reader = new DtcgYamlReader(),
    ) {
    }

    public function forBuiltinVariant(
        BuiltinThemeVariant $variant,
        ThemePaletteRecipe $paletteRecipe,
    ): LayerStack {
        $anchorProfile = null;

        $base = $this->paletteGenerator
            ->materializeDtcgDocument(
                $paletteRecipe,
                $variant->lineage(),
                $anchorProfile,
            )
            ->asLayer('base:' . $variant->lineage(), LayerRole::Base);

        $designSystemId = $variant->designSystemId() ?: $this->designSystems->defaultId();
        $designSystem = $this->designSystems->get($designSystemId);

        $themeTokens = (new MonoToneThemeLayerRewriter())->rewrite(
            $this->reader->read($variant->layerPath())->flatten(),
            $variant->tone(),
        );

        $theme = new TokenLayer('theme:' . $variant->id(), LayerRole::Theme, $themeTokens);

        return new LayerStack($base, $designSystem, $theme);
    }

    /**
     * @return list<TokenLayerInterface>
     */
    public function layerList(LayerStack $stack): array
    {
        return $stack->ordered();
    }
}
