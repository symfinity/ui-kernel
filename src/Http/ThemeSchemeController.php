<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Http;

use InvalidArgumentException;
use Symfinity\UiKernel\Theme\ActiveThemeContext;
use Symfinity\UiKernel\Theme\ThemeColorScheme;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class ThemeSchemeController
{
    public function __construct(
        private readonly ActiveThemeContext $context,
        private readonly ThemeSchemeResponder $responder,
    ) {
    }

    public function update(Request $request): JsonResponse
    {
        try {
            $scheme = $this->parseScheme($request);
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        if ($scheme === null) {
            return $this->responder->respondFromRequest($request);
        }

        return $this->responder->respond(
            $request,
            $this->context->preferenceFromRequest($request)->withScheme($scheme),
        );
    }

    private function parseScheme(Request $request): ?ThemeColorScheme
    {
        $raw = '';
        if ($request->getContent() !== '') {
            /** @var mixed $decoded */
            $decoded = json_decode($request->getContent(), true);
            if (is_array($decoded) && isset($decoded['scheme']) && is_string($decoded['scheme'])) {
                $raw = $decoded['scheme'];
            }
        }

        if ($raw === '' && $request->request->has('scheme')) {
            $raw = $request->request->getString('scheme');
        }

        if ($raw === '') {
            return null;
        }

        $scheme = ThemeColorScheme::tryFromString($raw);
        if ($scheme === null) {
            throw new InvalidArgumentException(sprintf('Invalid scheme "%s"; expected auto, light, or dark.', $raw));
        }

        return $scheme;
    }
}
