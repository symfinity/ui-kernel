<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Component;

use Symfinity\UiKernel\Page\UiFragment;

final class Card extends UiComponent
{
    public function __construct(
        private readonly string $title,
        private readonly string $body,
    ) {
        parent::__construct('card', 'default', [], new UiFragment('card-gallery'));
    }

    public function title(): string
    {
        return $this->title;
    }

    public function body(): string
    {
        return $this->body;
    }
}
