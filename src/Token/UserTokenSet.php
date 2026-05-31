<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use InvalidArgumentException;

/**
 * Partial integrator overrides merged after flavour resolution.
 */
final class UserTokenSet
{
    /**
     * @param array<string, string> $tokens
     */
    public function __construct(private readonly array $tokens = [])
    {
        foreach ($tokens as $key => $value) {
            if (!is_string($key) || !str_starts_with($key, '--ui-')) {
                throw new InvalidArgumentException(sprintf('Invalid user token key "%s".', $key));
            }
            if ($value === '') {
                throw new InvalidArgumentException(sprintf('User token "%s" must not be empty.', $key));
            }
        }
    }

    public function isEmpty(): bool
    {
        return $this->tokens === [];
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->tokens;
    }

    /**
     * @param array<string, string> $base
     *
     * @return array<string, string>
     */
    public function merge(array $base, string $schemaVersion): array
    {
        $allowed = array_flip(ThemeTokenSchema::requiredKeys($schemaVersion));

        foreach (array_keys($this->tokens) as $key) {
            if (!isset($allowed[$key])) {
                throw new InvalidArgumentException(sprintf(
                    'Unknown user token "%s" for schema %s.',
                    $key,
                    $schemaVersion,
                ));
            }
        }

        return [...$base, ...$this->tokens];
    }
}
