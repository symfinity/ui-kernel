<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Contract\Layer;

use Symfinity\UiKernel\Contract\Token\Token;

/**
 * An ordered set of token layers merged by role precedence (076).
 *
 * Merge is whole-token replace by path: a higher-precedence layer's token replaces
 * a lower one with the same path (no deep value merge). At most one layer per role.
 */
final class LayerStack
{
    /** @var list<TokenLayerInterface> */
    private readonly array $layers;

    public function __construct(TokenLayerInterface ...$layers)
    {
        $this->layers = array_values($layers);
    }

    /**
     * Layers ordered low to high precedence (base, design_system, theme).
     *
     * @return list<TokenLayerInterface>
     */
    public function ordered(): array
    {
        $layers = $this->layers;
        usort(
            $layers,
            static fn (TokenLayerInterface $a, TokenLayerInterface $b): int => $a->role()->precedence() <=> $b->role()->precedence(),
        );

        return $layers;
    }

    /**
     * Merge all layers into a path-string keyed token map.
     *
     * Existing keys keep their position when overridden; new keys append in layer order.
     *
     * @return array<string, Token>
     */
    public function merge(): array
    {
        $merged = [];
        foreach ($this->ordered() as $layer) {
            foreach ($layer->tokens() as $path => $token) {
                $merged[$path] = $token;
            }
        }

        return $merged;
    }

    /**
     * Stable signature of contributing layer ids in precedence order (cache key, FR-013).
     */
    public function signature(): string
    {
        $parts = [];
        foreach ($this->ordered() as $layer) {
            $parts[] = $layer->role()->value . ':' . $layer->id();
        }

        return hash('sha256', implode('|', $parts));
    }
}
