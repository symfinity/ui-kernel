<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Component;

final class FormRow extends UiComponent
{
    public function __construct(
        private readonly string $label,
        private readonly string $inputType = 'text',
    ) {
        parent::__construct('form-row', 'default', ['disabled' => 'true']);
    }

    public function label(): string
    {
        return $this->label;
    }

    public function inputType(): string
    {
        return $this->inputType;
    }
}
