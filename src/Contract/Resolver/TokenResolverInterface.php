<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Contract\Resolver;

use Symfinity\UiKernel\Contract\Layer\LayerStack;

/**
 * Merges a layer stack and resolves all aliases to concrete values (076).
 */
interface TokenResolverInterface
{
    public function resolve(LayerStack $stack): ResolvedGraphInterface;
}
