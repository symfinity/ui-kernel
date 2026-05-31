<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Component;

use Symfinity\UiKernel\Page\UiFragment;

final class GenericUiComponent extends UiComponent
{
    /**
     * @param array<string, string>       $state
     * @param array<string, string>       $slots
     */
    public function __construct(
        string $role,
        string $variant = 'default',
        array $state = [],
        ?UiFragment $fragment = null,
        private readonly array $slots = [],
    ) {
        parent::__construct($role, $variant, $state, $fragment);
    }

    public function slot(string $name, string $default = ''): string
    {
        return $this->slots[$name] ?? $default;
    }
}
