<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Contract\Layer;

use Symfinity\UiKernel\Contract\Token\Token;

/**
 * A named source of DTCG tokens carrying a role (076).
 */
interface TokenLayerInterface
{
    /**
     * Stable identity (folded into the cache key / layer signature).
     */
    public function id(): string;

    public function role(): LayerRole;

    /**
     * Flattened tokens keyed by their dotted path string.
     *
     * @return array<string, Token>
     */
    public function tokens(): array;
}
