<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Palette;

/**
 * OKLCH colour tuple — internal palette SSOT representation.
 */
final readonly class OklchTuple
{
    public function __construct(
        public float $l,
        public float $c,
        public float $h,
        public ?float $alpha = null,
    ) {
    }

    public function withAlpha(?float $alpha): self
    {
        return new self($this->l, $this->c, $this->h, $alpha);
    }
}
