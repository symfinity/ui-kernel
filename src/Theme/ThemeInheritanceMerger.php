<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

use Symfinity\UiKernel\Contract\Token\Token;
use Symfinity\UiKernel\Dtcg\BuiltinThemeVariant;
use Symfinity\UiKernel\Dtcg\DtcgYamlReader;
use Symfinity\UiKernel\Dtcg\MonoToneThemeLayerRewriter;

/**
 * Merges theme-layer DTCG tokens along an inheritance chain root → leaf (140).
 */
final class ThemeInheritanceMerger
{
    public function __construct(
        private readonly ThemeInheritanceGraph $graph,
        private readonly DtcgYamlReader $reader = new DtcgYamlReader(),
        private readonly MonoToneThemeLayerRewriter $rewriter = new MonoToneThemeLayerRewriter(),
    ) {
    }

    /**
     * @return array<string, Token>
     */
    public function mergedThemeTokens(BuiltinThemeVariant $leaf): array
    {
        /** @var array<string, Token> $accumulator */
        $accumulator = [];

        foreach ($this->graph->resolveVariantChain($leaf) as $variant) {
            $layerTokens = $this->rewriter->rewrite(
                $this->reader->read($variant->layerPath())->flatten(),
                $variant->tone(),
            );

            foreach ($layerTokens as $path => $token) {
                $accumulator[$path] = $token;
            }
        }

        return $accumulator;
    }
}
