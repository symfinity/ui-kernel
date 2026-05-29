<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Component;

use Symfinity\UiKernel\Page\UiFragment;

abstract class UiComponent
{
    /** @var list<UiComponent> */
    private array $children = [];

    public function __construct(
        private readonly string $role,
        private readonly string $variant = 'default',
        /** @var array<string, string> */
        private readonly array $state = [],
        private readonly ?UiFragment $fragment = null,
    ) {
    }

    public function role(): string
    {
        return $this->role;
    }

    public function variant(): string
    {
        return $this->variant;
    }

    /**
     * @return array<string, string>
     */
    public function state(): array
    {
        return $this->state;
    }

    public function fragment(): ?UiFragment
    {
        return $this->fragment;
    }

    public function addChild(UiComponent $child): static
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * @return list<UiComponent>
     */
    public function children(): array
    {
        return $this->children;
    }
}
