<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ThemeShellPresenter
{
    public function __construct(
        private readonly ActiveThemeContext $activeThemeContext,
        private readonly ThemeRegistry $themeRegistry,
        private readonly UrlGeneratorInterface $router,
    ) {
    }

    public function forRequest(Request $request, ?string $fallbackRoute = null): ThemeShellView
    {
        $routeName = $this->resolveRouteName($request, $fallbackRoute);
        if ($routeName === null) {
            return ThemeShellView::empty();
        }

        $activeTheme = $this->activeThemeContext->activeThemeFromRequest($request);
        $preference = $this->activeThemeContext->preferenceFromRequest($request);

        return new ThemeShellView(
            activeTheme: $activeTheme,
            scheme: $preference->scheme->value,
            colorScheme: $this->colorSchemeFromTheme($activeTheme),
            schemeSwitcherLinks: $this->schemeSwitcherLinks($request, $routeName, $preference),
        );
    }

    /**
     * @return list<array{id: string, url: string, active: bool}>
     */
    public function themeSwitcherLinks(Request $request, ?string $fallbackRoute = null): array
    {
        $routeName = $this->resolveRouteName($request, $fallbackRoute);
        if ($routeName === null) {
            return [];
        }

        $activeTheme = $this->activeThemeContext->activeThemeFromRequest($request);
        $params = $this->routeParams($request);

        $links = [];
        foreach ($this->allThemeIds() as $id) {
            $linkParams = $params;
            $linkParams['theme'] = $id;
            $links[] = [
                'id' => $id,
                'url' => $this->router->generate($routeName, $linkParams),
                'active' => $activeTheme->id() === $id,
            ];
        }

        return $links;
    }

    /**
     * @return list<array{scheme: string, url: string, active: bool}>
     */
    private function schemeSwitcherLinks(Request $request, string $routeName, ThemePreference $preference): array
    {
        $params = $this->routeParams($request);
        $links = [];

        foreach ([ThemeColorScheme::Auto, ThemeColorScheme::Light, ThemeColorScheme::Dark] as $scheme) {
            $linkParams = $params;
            $linkParams['scheme'] = $scheme->value;
            $links[] = [
                'scheme' => $scheme->value,
                'url' => $this->router->generate($routeName, $linkParams),
                'active' => $preference->scheme === $scheme,
            ];
        }

        return $links;
    }

    /**
     * @return array<string, mixed>
     */
    private function routeParams(Request $request): array
    {
        $params = array_merge(
            $request->attributes->get('_route_params', []),
            $request->query->all(),
        );
        unset($params['theme'], $params['scheme']);

        return $params;
    }

    private function resolveRouteName(Request $request, ?string $fallbackRoute): ?string
    {
        $route = $request->attributes->get('_route');
        if (\is_string($route) && $route !== '') {
            return $route;
        }

        if ($fallbackRoute !== null && $fallbackRoute !== '') {
            return $fallbackRoute;
        }

        return null;
    }

    private function colorSchemeFromTheme(Theme $theme): string
    {
        return ThemeLineageCatalog::isDarkThemeId($theme->id()) ? 'dark' : 'light';
    }

    /**
     * @return list<string>
     */
    private function allThemeIds(): array
    {
        $ids = array_map(static fn (Theme $theme): string => $theme->id(), $this->themeRegistry->all());
        sort($ids);

        return $ids;
    }
}
