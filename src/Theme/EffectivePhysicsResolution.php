<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

/**
 * Result of applying the physics mode matrix (111).
 */
final readonly class EffectivePhysicsResolution
{
    public function __construct(
        public PhysicsId $requested,
        public PhysicsId $effective,
        public bool $corrected,
        public ?string $correctionReason = null,
    ) {
    }
}
