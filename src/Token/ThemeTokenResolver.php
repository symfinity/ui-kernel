<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use Symfinity\UiKernel\Theme\EffectivePhysicsResolver;
use Symfinity\UiKernel\Theme\PhysicsRegistry;

final class ThemeTokenResolver
{
    public function __construct(
        private readonly SemanticColorMap $semanticColorMap = new SemanticColorMap(),
        private readonly PresetRegistry $presetRegistry = new PresetRegistry(),
        private readonly CompoundShadowBuilder $compoundShadowBuilder = new CompoundShadowBuilder(),
        private readonly PhysicsRegistry $physicsRegistry = new PhysicsRegistry(),
        private readonly EffectivePhysicsResolver $physicsResolver = new EffectivePhysicsResolver(),
    ) {
    }

    public function resolve(AuthoringThemeConfig $config, ?UserTokenSet $userTokens = null): DesignTokenSet
    {
        $schemaVersion = $config->schemaVersion();
        ThemeTokenSchema::requiredKeys($schemaVersion);

        $colors = $this->semanticColorMap->resolve(
            $config->colorRefs(),
            $config->paletteRecipe(),
            $config->tone(),
        );

        $appearance = $config->appearanceTokens();
        if ($appearance === []) {
            $appearance = $this->presetRegistry->tokensFor($config->layout(), $schemaVersion);
        }

        $effectivePhysics = $this->physicsResolver->resolve(
            $config->physics(),
            $this->physicsResolver->variantIsDark($config->id()),
        )->effective;

        $appearance = [
            ...$appearance,
            ...$this->physicsRegistry->appearanceResolveTokens($effectivePhysics),
        ];

        $merged = [...$appearance, ...$colors];

        if ($userTokens !== null && !$userTokens->isEmpty()) {
            $merged = $userTokens->merge($merged, $schemaVersion);
        }

        $merged = $this->compoundShadowBuilder->applyToTokenMap(
            $merged,
            LineageId::fromLayoutProfile($config->layout()),
            ColorMode::Light,
        );

        $merged = [...$merged, ...self::overlayTokens($merged, $config)];
        $merged = [...$merged, ...GlassSurfaceTokens::resolve($merged)];
        $merged = [...$merged, ...(new SemanticColorDerivatives())->derive($merged)];

        return DesignTokenSet::fromArray($merged, $schemaVersion);
    }

    /**
     * @param array<string, string> $merged
     *
     * @return array<string, string>
     */
    private static function overlayTokens(array $merged, AuthoringThemeConfig $config): array
    {
        return [
            '--ui-overlay-surface' => $merged['--ui-color-surface-elevated'],
            '--ui-overlay-border' => $merged['--ui-color-border'],
            '--ui-overlay-shadow' => $merged['--ui-shadow-lg'],
            '--ui-backdrop-color' => $merged['--ui-color-overlay'],
            '--ui-backdrop-blur' => $config->backdropBlur(),
        ];
    }
}
