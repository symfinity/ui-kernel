<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Twig;

use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Flavour\FlavourRegistry;
use Symfinity\UiKernel\Page\UiPage;
use Symfinity\UiKernel\Renderer\HtmlRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class UiKernelExtension extends AbstractExtension
{
    public function __construct(
        private readonly HtmlRenderer $htmlRenderer,
        private readonly CssGenerator $cssGenerator,
        private readonly FlavourRegistry $flavourRegistry,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('ui_kernel_page', $this->renderPage(...), ['is_safe' => ['html']]),
            new TwigFunction('ui_kernel_css', $this->renderCss(...), ['is_safe' => ['html']]),
        ];
    }

    public function renderPage(UiPage $page): string
    {
        return $this->htmlRenderer->render($page);
    }

    public function renderCss(?string $themeId = null): string
    {
        $flavour = $this->flavourRegistry->resolve($themeId);

        return sprintf(
            "<style id=\"ui-kernel-theme-css\">\n%s\n</style>",
            $this->cssGenerator->forFlavour($flavour),
        );
    }
}
