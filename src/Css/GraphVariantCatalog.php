<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Css;

use Symfinity\UiKernel\Contract\Catalog\GraphVariantCatalogPort;
use Symfinity\UiKernel\Contract\Resolver\ResolvedGraphInterface;
use Symfinity\UiKernel\Dtcg\BuiltinDtcgThemeCatalog;
use Symfinity\UiKernel\Dtcg\ThemeDtcgResolver;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfinity\UiKernel\Token\SemanticColourVocabulary;

/**
 * Default {@see GraphVariantCatalogPort} — derives slugs from the active resolved graph (078).
 */
final class GraphVariantCatalog implements GraphVariantCatalogPort
{
    public function __construct(
        private readonly ThemeDtcgResolver $themeDtcgResolver,
        private readonly BuiltinDtcgThemeCatalog $themeCatalog,
        #[Autowire('%symfinity.ui_kernel.default_theme%')]
        private readonly string $defaultThemeId = 'semantic',
    ) {
    }

    public function semanticColorSlugs(): array
    {
        return SemanticColourVocabulary::fromGraph($this->resolvedGraph())->all();
    }

    public function layerSignature(): string
    {
        return $this->resolvedGraph()->layerSignature();
    }

    private function resolvedGraph(): ResolvedGraphInterface
    {
        return $this->themeDtcgResolver->resolvedGraph(
            $this->themeCatalog->get($this->defaultThemeId),
        );
    }
}
