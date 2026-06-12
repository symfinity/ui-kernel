<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Css;

use Symfinity\UiKernel\Profile\SystemProfile;

/**
 * CSS generation cache-key contract (018 FR-018).
 */
final class CssCacheKeyPolicy
{
    private const TOKENS_ONLY_VERSION = 'tokens-only:1';

    public static function roleRulesVersion(): string
    {
        return self::TOKENS_ONLY_VERSION;
    }

    /**
     * @return array{
     *     themeId: string,
     *     userTokenHash: string,
     *     schemaVersion: string,
     *     presetHash: string,
     *     roleRulesVersion: string,
     *     systemProfileId: string,
     *     profileHash: string
     * }
     */
    public static function parts(
        string $themeId,
        string $userTokenHash,
        string $schemaVersion,
        string $presetHash,
        SystemProfile $profile,
    ): array {
        return [
            'themeId' => $themeId,
            'userTokenHash' => $userTokenHash,
            'schemaVersion' => $schemaVersion,
            'presetHash' => $presetHash,
            'roleRulesVersion' => self::roleRulesVersion(),
            'systemProfileId' => $profile->id,
            'profileHash' => $profile->hash(),
        ];
    }

    /**
     * @param array<string, string> $parts
     */
    public static function fingerprint(array $parts): string
    {
        ksort($parts);

        return hash('sha256', json_encode($parts, JSON_THROW_ON_ERROR));
    }
}
