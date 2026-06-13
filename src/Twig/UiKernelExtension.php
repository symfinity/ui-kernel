<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Twig;

use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ActiveThemeContext;
use Symfinity\UiKernel\Theme\ThemeShellPresenter;
use Symfinity\UiKernel\Theme\ThemeShellView;
use Symfinity\UiKernel\Theme\ThemeRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class UiKernelExtension extends AbstractExtension
{
    private const CSS_BYTES_REQUEST_ATTR = '_symfinity_ui_kernel_css_bytes';

    public function __construct(
        private readonly CssGenerator $cssGenerator,
        private readonly ThemeRegistry $themeRegistry,
        private readonly ActiveThemeContext $activeThemeContext,
        private readonly ThemeShellPresenter $themeShellPresenter,
        private readonly RequestStack $requestStack,
        private readonly Environment $twig,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('ui_kernel_css', $this->renderCss(...), ['is_safe' => ['html']]),
            new TwigFunction('ui_kernel_theme_boot_script', $this->renderBootScript(...), ['is_safe' => ['html']]),
            new TwigFunction('ui_kernel_active_theme_id', $this->activeThemeId(...)),
            new TwigFunction('ui_kernel_theme_shell', $this->themeShell(...)),
        ];
    }

    public function themeShell(?string $fallbackRoute = null): ThemeShellView
    {
        $request = $this->requestStack->getCurrentRequest()
            ?? \Symfony\Component\HttpFoundation\Request::create('/');

        return $this->themeShellPresenter->forRequest($request, $fallbackRoute);
    }

    public function renderCss(?string $themeId = null): string
    {
        $css = '';
        if ($themeId !== null && $themeId !== '') {
            $css = $this->cssGenerator->forTheme($this->themeRegistry->resolve($themeId));
        } else {
            $request = $this->requestStack->getCurrentRequest();
            if ($request === null) {
                $css = $this->cssGenerator->forTheme($this->themeRegistry->resolve(null));
            } else {
                foreach ($this->activeThemeContext->cssThemesFromRequest($request) as $theme) {
                    $css .= $this->cssGenerator->forTheme($theme) . "\n";
                }
            }
        }

        $this->recordCssBytes($css);

        return sprintf("<style id=\"ui-kernel-theme-css\">\n%s\n</style>", trim($css));
    }

    private function recordCssBytes(string $css): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null || $css === '') {
            return;
        }

        $attr = self::CSS_BYTES_REQUEST_ATTR;
        $raw = $request->attributes->get($attr, 0);
        $existing = is_int($raw) ? $raw : (is_numeric($raw) ? (int) $raw : 0);
        $request->attributes->set($attr, $existing + \strlen($css));
    }

    public function renderBootScript(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return '';
        }

        return $this->twig->render('@UiKernel/_theme_boot_script.html.twig', [
            'boot_config' => $this->activeThemeContext->bootConfigFromRequest($request),
        ]);
    }

    public function activeThemeId(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return $this->themeRegistry->resolve(null)->id();
        }

        return $this->activeThemeContext->resolvedThemeIdFromRequest($request);
    }
}
