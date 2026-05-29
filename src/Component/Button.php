<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Component;

final class Button extends UiComponent
{
    public function __construct(
        string $variant,
        private readonly string $label,
        /** @var array<string, string> */
        array $state = [],
    ) {
        parent::__construct('button', $variant, $state);
    }

    public function label(): string
    {
        return $this->label;
    }
}
