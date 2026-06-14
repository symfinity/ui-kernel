<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Contract\Layer;

use Symfinity\UiKernel\Contract\Token\Token;

/**
 * Immutable token layer value object (076).
 */
final class TokenLayer implements TokenLayerInterface
{
    /**
     * @param array<string, Token> $tokens flattened tokens keyed by dotted path string
     */
    public function __construct(
        private readonly string $id,
        private readonly LayerRole $role,
        private readonly array $tokens,
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function role(): LayerRole
    {
        return $this->role;
    }

    /**
     * @return array<string, Token>
     */
    public function tokens(): array
    {
        return $this->tokens;
    }
}
