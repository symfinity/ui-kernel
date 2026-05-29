<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Component;

final class Alert extends UiComponent
{
    public function __construct(
        string $variant,
        private readonly string $message,
    ) {
        parent::__construct('alert', $variant);
    }

    public function message(): string
    {
        return $this->message;
    }
}
