<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

final class ThemeTokenResolver
{
    public function __construct(
        private readonly SemanticColorMap $semanticColorMap = new SemanticColorMap(),
        private readonly PresetRegistry $presetRegistry = new PresetRegistry(),
    ) {
    }

    public function resolve(ThemeConfig $config, ?UserTokenSet $userTokens = null): DesignTokenSet
    {
        $schemaVersion = $config->schemaVersion();
        $colors = $this->semanticColorMap->resolve($config->colorRefs(), $config->paletteRecipe());

        if ($schemaVersion === ThemeTokenSchema::V1_0) {
            $colors = array_intersect_key($colors, array_flip(ThemeTokenSchema::COLOR_KEYS_V1));
        }

        $layout = $this->presetRegistry->tokensFor($config->layout(), $schemaVersion);
        $merged = [...$layout, ...$colors];

        if ($userTokens !== null && !$userTokens->isEmpty()) {
            $merged = $userTokens->merge($merged, $schemaVersion);
        }

        if ($schemaVersion === ThemeTokenSchema::V2_0) {
            $merged = [...$merged, ...self::overlayTokens($merged, $config)];
        }

        return DesignTokenSet::fromArray($merged, $schemaVersion);
    }

    /**
     * @param array<string, string> $merged
     *
     * @return array<string, string>
     */
    private static function overlayTokens(array $merged, ThemeConfig $config): array
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
