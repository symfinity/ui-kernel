<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Detects cyclic theme/preset inheritance chains (018 FR-017).
 */
final class PresetCycleGuard
{
    /**
     * @param list<string> $chain preset ids from leaf toward root
     */
    public static function assertAcyclic(array $chain): void
    {
        $seen = [];
        foreach ($chain as $id) {
            if (isset($seen[$id])) {
                ThemeErrorCatalog::throw(
                    ThemeErrorCatalog::PRESET_CYCLE,
                    sprintf('Preset cycle detected at "%s".', $id),
                );
            }
            $seen[$id] = true;
        }
    }
}
