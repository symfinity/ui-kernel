<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Twig;

use Symfinity\UiKernel\Theme\ThemeShellPresenter;
use Symfinity\UiKernel\Theme\ThemeShellView;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * Exposes theme shell view-model as Twig globals for SchemeSwitch + layout data-theme hooks.
 */
final class ThemeShellTwigExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ThemeShellPresenter $themeShellPresenter,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getGlobals(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return ThemeShellView::empty()->toArray();
        }

        return $this->themeShellPresenter->forRequest($request)->toArray();
    }
}
