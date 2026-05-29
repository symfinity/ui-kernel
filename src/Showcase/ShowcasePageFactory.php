<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Showcase;

use Symfinity\UiKernel\Component\Alert;
use Symfinity\UiKernel\Component\Button;
use Symfinity\UiKernel\Component\Card;
use Symfinity\UiKernel\Component\FormRow;
use Symfinity\UiKernel\Page\UiPage;

final class ShowcasePageFactory
{
    public function create(): UiPage
    {
        $page = new UiPage('UI Kernel theme showcase');

        $page
            ->add(new Button('primary', 'Primary action'))
            ->add(new Button('secondary', 'Secondary action'))
            ->add(new Button('danger', 'Danger action'))
            ->add(new Button('success', 'Success action'))
            ->add(new Card('Gallery card', 'Fixed component tree — only tokens and data-theme change between flavours.'))
            ->add(new Alert('danger', 'Themes are Symfinity token packs inspired by common systems, not official Bootstrap or Tailwind.'))
            ->add(new FormRow('Sample field'));

        return $page;
    }
}
