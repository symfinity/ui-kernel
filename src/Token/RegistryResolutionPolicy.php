<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Deterministic registry collision handling (018 FR-016).
 */
final class RegistryResolutionPolicy
{
    /**
     * @param list<array{id: string, priority: int}> $providers
     *
     * @return list<string>
     */
    public static function sortedProviderIds(array $providers): array
    {
        usort(
            $providers,
            static function (array $a, array $b): int {
                $priority = $b['priority'] <=> $a['priority'];
                if ($priority !== 0) {
                    return $priority;
                }

                return $a['id'] <=> $b['id'];
            },
        );

        return array_column($providers, 'id');
    }

    public static function assertUniqueThemeId(string $id, bool $alreadyRegistered): void
    {
        if ($alreadyRegistered) {
            ThemeErrorCatalog::throw(
                ThemeErrorCatalog::DUPLICATE_THEME_ID,
                sprintf('Duplicate theme id "%s".', $id),
            );
        }
    }

    /**
     * @param list<string> $ids
     */
    public static function assertNoDuplicateProviderIds(array $ids): void
    {
        $seen = [];
        foreach ($ids as $id) {
            if (isset($seen[$id])) {
                ThemeErrorCatalog::throw(
                    ThemeErrorCatalog::REGISTRY_COLLISION,
                    sprintf('Duplicate provider id "%s".', $id),
                );
            }
            $seen[$id] = true;
        }
    }
}
