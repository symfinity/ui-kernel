<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Contract\Token;

/**
 * A resolved-or-aliased DTCG design token (076).
 */
interface TokenInterface
{
    public function path(): TokenPath;

    public function type(): TokenType;

    /**
     * Concrete structured value or an {@see AliasReference} when this token is an alias.
     */
    public function value(): mixed;

    public function isAlias(): bool;

    public function description(): ?string;

    /**
     * @return array<string, mixed>
     */
    public function extensions(): array;
}
