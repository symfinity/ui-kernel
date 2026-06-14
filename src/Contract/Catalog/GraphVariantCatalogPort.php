<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Contract\Catalog;

/**
 * Read-only semantic colour slug list from the active resolved graph (078).
 */
interface GraphVariantCatalogPort
{
    /**
     * @return list<string> kebab-case slugs derived from {@code color.*} tokens
     */
    public function semanticColorSlugs(): array;

    /**
     * Stable hash when active theme/design-system/base layers change.
     */
    public function layerSignature(): string;
}
