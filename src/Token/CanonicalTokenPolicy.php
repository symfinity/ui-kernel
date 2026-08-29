<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Canonical token names only — rejects legacy aliases (018 FR-015).
 */
final class CanonicalTokenPolicy
{
    /** @var array<string, string> forbidden => canonical hint */
    public const FORBIDDEN_ALIASES = [
        '--ui-color-focus-ring' => '--ui-color-focus',
        '--ui-color-status-error' => '--ui-color-danger',
        '--ui-transition-duration' => '--ui-motion-duration-normal',
    ];

    public static function assertCanonicalKey(string $key): void
    {
        if (isset(self::FORBIDDEN_ALIASES[$key])) {
            ThemeErrorCatalog::throw(
                ThemeErrorCatalog::FORBIDDEN_TOKEN_ALIAS,
                sprintf(
                    'Token "%s" is forbidden; use "%s".',
                    $key,
                    self::FORBIDDEN_ALIASES[$key],
                ),
            );
        }
    }

    /**
     * @param array<string, string> $tokens
     */
    public static function assertCanonicalKeys(array $tokens): void
    {
        foreach (array_keys($tokens) as $key) {
            self::assertCanonicalKey($key);
        }
    }
}
