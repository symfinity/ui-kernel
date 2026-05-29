<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use InvalidArgumentException;

final readonly class DesignTokenSet
{
    /**
     * @param array<string, string> $tokens
     */
    public function __construct(private array $tokens)
    {
        self::assertComplete($tokens);
    }

    /**
     * @param array<string, string> $tokens
     */
    public static function fromArray(array $tokens): self
    {
        return new self($tokens);
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->tokens;
    }

    /**
     * @param array<string, string> $tokens
     */
    public static function assertComplete(array $tokens): void
    {
        foreach (ThemeTokenSchema::REQUIRED_KEYS as $key) {
            if (!isset($tokens[$key]) || $tokens[$key] === '') {
                throw new InvalidArgumentException(sprintf('Missing required theme token "%s".', $key));
            }
        }
    }
}
