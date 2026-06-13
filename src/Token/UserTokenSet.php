<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Partial integrator overrides merged after theme resolution.
 */
final class UserTokenSet
{
    /**
     * @param array<string, string> $tokens
     */
    public function __construct(private readonly array $tokens = [])
    {
        foreach ($tokens as $key => $value) {
            if (!str_starts_with($key, '--ui-')) {
                ThemeErrorCatalog::throw(
                    ThemeErrorCatalog::UNKNOWN_TOKEN_KEY,
                    sprintf('Invalid user token key "%s".', $key),
                );
            }
            CanonicalTokenPolicy::assertCanonicalKey($key);
            TokenValueValidator::assertValid($key, $value);
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
                ThemeErrorCatalog::throw(
                    ThemeErrorCatalog::UNKNOWN_TOKEN_KEY,
                    sprintf('Unknown user token "%s" for schema %s.', $key, $schemaVersion),
                );
            }
        }

        return [...$base, ...$this->tokens];
    }
}
