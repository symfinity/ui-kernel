<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Page;

/**
 * Stable fragment identifier for future action patches (004).
 */
final readonly class UiFragment
{
    public function __construct(
        public string $id,
    ) {
    }
}
