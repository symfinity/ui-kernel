<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Contract\Resolver;

use Symfinity\UiKernel\Contract\Token\TokenInterface;
use Symfinity\UiKernel\Contract\Token\TokenPath;

/**
 * The merged, alias-resolved token set for an active (design system, theme) selection (076).
 *
 * All tokens are concrete (no aliases remain).
 */
interface ResolvedGraphInterface
{
    public function get(TokenPath|string $path): TokenInterface;

    public function has(TokenPath|string $path): bool;

    /**
     * Concrete tokens keyed by dotted path string (insertion order preserved).
     *
     * @return array<string, TokenInterface>
     */
    public function all(): array;

    /**
     * Names under the `color.*` semantic group (variant derivation, FR-012; read-only).
     *
     * @return list<string>
     */
    public function semanticColors(): array;

    /**
     * Stable hash of contributing layer ids (cache key, FR-013).
     */
    public function layerSignature(): string;
}
