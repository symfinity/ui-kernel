<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Http;

use InvalidArgumentException;
use Symfinity\UiKernel\Theme\ActiveThemeContext;
use Symfinity\UiKernel\Theme\ThemePreferenceCookies;
use Symfinity\UiKernel\Theme\ThemePreferenceResolver;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ThemePreferenceRedirectHandler
{
    public function __construct(
        private readonly ThemePreferenceCookies $cookies,
        private readonly ThemePreferenceResolver $resolver,
        private readonly ActiveThemeContext $context,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function createRedirectIfPreferenceQueryPresent(Request $request): ?Response
    {
        if (!$request->query->has('theme') && !$request->query->has('scheme')) {
            return null;
        }

        try {
            $current = $this->context->preferenceFromRequest($request);
            $preference = $this->resolver->applyQueryOverrides($request, $current);
        } catch (InvalidArgumentException) {
            return null;
        }

        $response = new RedirectResponse($this->targetUrl($request), Response::HTTP_FOUND);
        $this->cookies->attach($response, $preference);

        return $response;
    }

    private function targetUrl(Request $request): string
    {
        $route = $request->attributes->getString('_route');
        if ($route !== '') {
            /** @var array<string, mixed> $params */
            $params = $request->attributes->get('_route_params', []);
            foreach ($request->query->all() as $key => $value) {
                if ($key === 'theme' || $key === 'scheme') {
                    continue;
                }
                $params[$key] = $value;
            }

            return $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_PATH);
        }

        $query = $request->query->all();
        unset($query['theme'], $query['scheme']);
        $path = $request->getPathInfo();
        $queryString = http_build_query($query);

        return $request->getSchemeAndHttpHost() . $path . ($queryString !== '' ? '?' . $queryString : '');
    }
}
