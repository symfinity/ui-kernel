<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

use Psr\Log\LoggerInterface;

/**
 * Applies glass+light → flat correction per physics-mode-matrix contract (111).
 */
final class EffectivePhysicsResolver
{
    public function __construct(
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public function resolve(PhysicsId $requested, bool $variantIsDark): EffectivePhysicsResolution
    {
        if ($requested === PhysicsId::Glass && !$variantIsDark) {
            $resolution = new EffectivePhysicsResolution(
                requested: $requested,
                effective: PhysicsId::Flat,
                corrected: true,
                correctionReason: 'glass unavailable in light mode',
            );
            $this->logger?->warning(
                'ui-kernel physics correction: requested {requested} on light variant; effective {effective}. {reason}',
                [
                    'requested' => $requested->value,
                    'effective' => $resolution->effective->value,
                    'reason' => $resolution->correctionReason,
                ],
            );

            return $resolution;
        }

        return new EffectivePhysicsResolution(
            requested: $requested,
            effective: $requested,
            corrected: false,
        );
    }

    public function resolveFromStrings(string $requested, bool $variantIsDark): EffectivePhysicsResolution
    {
        return $this->resolve(PhysicsId::fromString($requested), $variantIsDark);
    }

    public function variantIsDark(string $themeId): bool
    {
        return str_ends_with($themeId, '-dark') || $themeId === 'default-dark';
    }
}
