<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Contract\Token;

/**
 * Immutable DTCG token value object (076).
 */
final class Token implements TokenInterface
{
    /**
     * @param mixed                $value      concrete structured value or an AliasReference
     * @param array<string, mixed> $extensions opaque DTCG `$extensions`
     */
    public function __construct(
        private readonly TokenPath $path,
        private readonly TokenType $type,
        private readonly mixed $value,
        private readonly ?string $description = null,
        private readonly array $extensions = [],
    ) {
    }

    public function path(): TokenPath
    {
        return $this->path;
    }

    public function type(): TokenType
    {
        return $this->type;
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function isAlias(): bool
    {
        return $this->value instanceof AliasReference;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    /**
     * @return array<string, mixed>
     */
    public function extensions(): array
    {
        return $this->extensions;
    }

    /**
     * Return a concrete copy with the alias resolved to a structured value and effective type.
     */
    public function withResolvedValue(mixed $value, TokenType $type): self
    {
        return new self($this->path, $type, $value, $this->description, $this->extensions);
    }
}
