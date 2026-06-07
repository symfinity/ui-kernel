<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Http;

use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ActiveThemeContext;
use Symfinity\UiKernel\Theme\ThemePreference;
use Symfinity\UiKernel\Theme\ThemePreferenceCookies;
use Symfinity\UiKernel\Theme\ThemePreferenceResolver;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ThemeSchemeResponder
{
    public function __construct(
        private readonly ActiveThemeContext $context,
        private readonly ThemePreferenceResolver $resolver,
        private readonly ThemePreferenceCookies $cookies,
        private readonly CssGenerator $cssGenerator,
    ) {
    }

    public function respond(Request $request, ThemePreference $preference): JsonResponse
    {
        $theme = $this->resolver->resolveTheme($preference, $this->resolver->resolveSystemPrefersDark($request));
        $themeId = $theme->id();

        $response = new JsonResponse([
            'themeId' => $themeId,
            'css' => $this->cssGenerator->forTheme($theme),
            'colorScheme' => str_ends_with($themeId, '-dark') ? 'dark' : 'light',
            'scheme' => $preference->scheme->value,
            'lineage' => $preference->lineage,
        ], Response::HTTP_OK);

        $this->cookies->attach($response, $preference);

        return $response;
    }

    public function respondFromRequest(Request $request): JsonResponse
    {
        return $this->respond($request, $this->context->preferenceFromRequest($request));
    }
}
