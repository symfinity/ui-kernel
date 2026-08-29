<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

final readonly class DesignTokenSet
{
    /**
     * @param array<string, string> $tokens
     */
    public function __construct(
        private array $tokens,
        private string $schemaVersion = ThemeTokenSchema::V2_0,
    ) {
        self::assertComplete($tokens, $schemaVersion);
    }

    /**
     * @param array<string, string> $tokens
     */
    public static function fromArray(array $tokens, string $schemaVersion = ThemeTokenSchema::V2_0): self
    {
        return new self($tokens, $schemaVersion);
    }

    public function schemaVersion(): string
    {
        return $this->schemaVersion;
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
    public static function assertComplete(array $tokens, string $schemaVersion = ThemeTokenSchema::V2_0): void
    {
        foreach (ThemeTokenSchema::requiredKeys($schemaVersion) as $key) {
            if (!isset($tokens[$key]) || $tokens[$key] === '') {
                ThemeErrorCatalog::throw(
                    ThemeErrorCatalog::MISSING_REQUIRED_TOKEN,
                    sprintf('Missing required theme token "%s" for schema %s.', $key, $schemaVersion),
                );
            }
        }

        foreach ($tokens as $key => $value) {
            CanonicalTokenPolicy::assertCanonicalKey($key);
            TokenValueValidator::assertValid($key, $value);
        }
    }
}
