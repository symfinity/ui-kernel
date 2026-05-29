<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Flavour;

use Symfinity\UiKernel\Token\DesignTokenSet;

interface Flavour
{
    public function id(): string;

    public function label(): string;

    public function tokens(): DesignTokenSet;
}
