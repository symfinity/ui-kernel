<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Contract\Token;

use InvalidArgumentException;

/**
 * Immutable dotted token address (DTCG), e.g. `color.blue.600`.
 *
 * Lives in the dependency-free Contract namespace (076 FR-010).
 */
final class TokenPath
{
    private const SEGMENT_PATTERN = '/^[A-Za-z0-9-]+$/';

    /** @var list<string> */
    private readonly array $segments;

    /**
     * @param list<string> $segments
     */
    private function __construct(array $segments)
    {
        if ($segments === []) {
            throw new InvalidArgumentException('Token path must have at least one segment.');
        }

        foreach ($segments as $segment) {
            if (preg_match(self::SEGMENT_PATTERN, $segment) !== 1) {
                throw new InvalidArgumentException(sprintf('Invalid token path segment "%s".', $segment));
            }
        }

        $this->segments = array_values($segments);
    }

    public static function fromString(string $path): self
    {
        return new self(explode('.', $path));
    }

    /**
     * @param list<string> $segments
     */
    public static function fromSegments(array $segments): self
    {
        return new self($segments);
    }

    public function child(string $segment): self
    {
        return new self([...$this->segments, $segment]);
    }

    /**
     * @return list<string>
     */
    public function segments(): array
    {
        return $this->segments;
    }

    public function first(): string
    {
        return $this->segments[0];
    }

    public function depth(): int
    {
        return \count($this->segments);
    }

    public function equals(self $other): bool
    {
        return $this->segments === $other->segments;
    }

    public function __toString(): string
    {
        return implode('.', $this->segments);
    }
}
