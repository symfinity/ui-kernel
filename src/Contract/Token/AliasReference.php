<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Contract\Token;

use InvalidArgumentException;

/**
 * A DTCG alias value of the form `{group.token}` (076).
 */
final class AliasReference
{
    private function __construct(
        private readonly TokenPath $target,
    ) {
    }

    /**
     * True when a raw DTCG `$value` is an alias string `"{path}"`.
     */
    public static function isAlias(mixed $value): bool
    {
        return \is_string($value)
            && \strlen($value) > 2
            && str_starts_with($value, '{')
            && str_ends_with($value, '}');
    }

    public static function parse(string $value): self
    {
        if (!self::isAlias($value)) {
            throw new InvalidArgumentException(sprintf('Value "%s" is not a DTCG alias.', $value));
        }

        $inner = substr($value, 1, -1);

        return new self(TokenPath::fromString($inner));
    }

    public function target(): TokenPath
    {
        return $this->target;
    }

    public function __toString(): string
    {
        return '{' . $this->target . '}';
    }
}
