<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

use Symfony\Component\HttpFoundation\Request;

final class ActiveThemeContext
{
    public function __construct(
        private readonly ThemePreferenceCookies $cookies,
        private readonly ThemePreferenceResolver $resolver,
        private readonly ThemeRegistry $themeRegistry,
    ) {
    }

    public function preferenceFromRequest(Request $request): ThemePreference
    {
        return $this->cookies->read($request, $this->resolver->defaultLineage());
    }

    public function activeThemeFromRequest(Request $request): Theme
    {
        $preference = $this->preferenceFromRequest($request);

        return $this->resolver->resolveTheme($preference, $this->resolver->systemPrefersDark($request));
    }

    public function resolvedThemeIdFromRequest(Request $request): string
    {
        $preference = $this->preferenceFromRequest($request);

        return $this->resolver->resolveThemeId($preference, $this->resolver->systemPrefersDark($request));
    }

    /**
     * @return list<Theme>
     */
    public function cssThemesFromRequest(Request $request): array
    {
        return [$this->activeThemeFromRequest($request)];
    }

    /**
     * @return array{
     *     scheme: string,
     *     lineage: string,
     *     defaultLineage: string,
     *     resolvedThemeId: string,
     *     schemeEndpoint: string,
     *     fallbackId: string,
     *     pairs: array<string, array{light: string, dark: string}>
     * }
     */
    public function bootConfigFromRequest(Request $request): array
    {
        $preference = $this->preferenceFromRequest($request);

        $resolvedThemeId = $this->resolvedThemeIdFromRequest($request);

        return [
            'scheme' => $preference->scheme->value,
            'lineage' => $preference->lineage,
            'defaultLineage' => $this->resolver->defaultLineage(),
            'resolvedThemeId' => $resolvedThemeId,
            'schemeEndpoint' => ThemeShellView::SCHEME_ENDPOINT,
            'fallbackId' => $this->resolver->resolveThemeId(
                ThemePreference::defaults($this->resolver->defaultLineage()),
                false,
            ),
            'pairs' => ThemeLineageCatalog::pairs(),
        ];
    }
}
