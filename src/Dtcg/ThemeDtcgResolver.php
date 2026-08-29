<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Dtcg;

use Symfinity\UiKernel\Contract\Resolver\ResolvedGraphInterface;
use Symfinity\UiKernel\Contract\Token\TokenInterface;
use Symfinity\UiKernel\Css\CssVariableSet;
use Symfinity\UiKernel\Token\ColorMode;
use Symfinity\UiKernel\Token\CompoundShadowBuilder;
use Symfinity\UiKernel\Token\DesignTokenSet;
use Symfinity\UiKernel\Token\GlassSurfaceTokens;
use Symfinity\UiKernel\Token\LineageId;
use Symfinity\UiKernel\Token\SemanticColorDerivatives;
use Symfinity\UiKernel\Token\SemanticColorMap;
use Symfinity\UiKernel\Token\ThemeTokenSchema;
use Symfinity\UiKernel\Token\UserTokenSet;

/**
 * Resolves a built-in DTCG theme variant to a {@see DesignTokenSet} (077 single path).
 */
final class ThemeDtcgResolver
{
    public function __construct(
        private readonly LayerStackBuilder $stackBuilder,
        private readonly LayeredTokenResolver $tokenResolver = new LayeredTokenResolver(),
        private readonly CssVariableSet $cssVariableSet = new CssVariableSet(),
        private readonly CssEmissionFilter $emissionFilter = new CssEmissionFilter(),
        private readonly SemanticColorDerivatives $derivatives = new SemanticColorDerivatives(),
        private readonly CompoundShadowBuilder $compoundShadowBuilder = new CompoundShadowBuilder(),
    ) {
    }

    public function resolve(BuiltinThemeVariant $variant, ?UserTokenSet $userTokens = null): DesignTokenSet
    {
        $stack = $this->stackBuilder->forBuiltinVariant($variant, $variant->paletteRecipe());
        $graph = $this->tokenResolver->resolve($stack);

        $variables = $this->cssVariableSet->fromEmitTokens($this->emitTokens($graph));

        if ($userTokens !== null && !$userTokens->isEmpty()) {
            $variables = $userTokens->merge($variables, $variant->schemaVersion());
        }

        $variables = $this->compoundShadowBuilder->applyToTokenMap(
            $variables,
            LineageId::fromThemeLineage($variant->lineage()),
            ColorMode::fromThemeMode($variant->mode()),
        );

        $variables = [...$variables, ...self::overlayTokens($variables, $variant)];
        $variables = [...$variables, ...GlassSurfaceTokens::resolve($variables)];
        $variables = [...$variables, ...$this->derivatives->derive($variables)];
        $variables = self::canonicalize($variables);

        return DesignTokenSet::fromArray($variables, $variant->schemaVersion());
    }

    public function resolvedGraph(BuiltinThemeVariant $variant): ResolvedGraphInterface
    {
        $stack = $this->stackBuilder->forBuiltinVariant($variant, $variant->paletteRecipe());

        return $this->tokenResolver->resolve($stack);
    }

    /**
     * @return array<string, TokenInterface>
     */
    private function emitTokens(ResolvedGraphInterface $graph): array
    {
        return $this->emissionFilter->emitTokens($graph);
    }

    /**
     * @param array<string, string> $merged
     *
     * @return array<string, string>
     */
    private static function overlayTokens(array $merged, BuiltinThemeVariant $variant): array
    {
        return [
            '--ui-overlay-surface' => $merged['--ui-color-surface-elevated'],
            '--ui-overlay-border' => $merged['--ui-color-border'],
            '--ui-overlay-shadow' => $merged['--ui-shadow-lg'],
            '--ui-backdrop-color' => $merged['--ui-color-overlay'],
            '--ui-backdrop-blur' => $variant->backdropBlur(),
        ];
    }

    /**
     * @param array<string, string> $variables
     *
     * @return array<string, string>
     */
    private static function canonicalize(array $variables): array
    {
        $order = [
            ...ThemeTokenSchema::LAYOUT_KEYS,
            ...array_values(SemanticColorMap::ROLE_TO_CSS),
            ...ThemeTokenSchema::OVERLAY_KEYS,
            ...ThemeTokenSchema::GLASS_KEYS,
        ];

        $ordered = [];
        foreach ($order as $key) {
            if (isset($variables[$key])) {
                $ordered[$key] = $variables[$key];
            }
        }

        foreach ($variables as $key => $value) {
            if (!isset($ordered[$key])) {
                $ordered[$key] = $value;
            }
        }

        return $ordered;
    }
}
