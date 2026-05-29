<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Page;

use Symfinity\UiKernel\Component\UiComponent;

final class UiPage
{
    /** @var list<UiComponent> */
    private array $children = [];

    public function __construct(
        private readonly string $title,
    ) {
    }

    public function title(): string
    {
        return $this->title;
    }

    public function add(UiComponent $component): self
    {
        $this->children[] = $component;

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
