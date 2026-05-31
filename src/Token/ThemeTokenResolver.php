<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

final class ThemeTokenResolver
{
    public function __construct(
        private readonly SemanticColorMap $semanticColorMap = new SemanticColorMap(),
        private readonly LineagePresetRegistry $lineagePresets = new LineagePresetRegistry(),
    ) {
    }

    public function resolve(FlavourThemeConfig $config, ?UserTokenSet $userTokens = null): DesignTokenSet
    {
        $schemaVersion = $config->schemaVersion();
        $colors = $this->semanticColorMap->resolve($config->colorRefs());

        if ($schemaVersion === ThemeTokenSchema::V1_0) {
            $colors = array_intersect_key($colors, array_flip(ThemeTokenSchema::COLOR_KEYS_V1));
        }

        $layout = $this->lineagePresets->tokensFor($config->layout(), $schemaVersion);
        $merged = [...$layout, ...$colors];

        if ($userTokens !== null && !$userTokens->isEmpty()) {
            $merged = $userTokens->merge($merged, $schemaVersion);
        }

        return DesignTokenSet::fromArray($merged, $schemaVersion);
    }
}
