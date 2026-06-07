<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\DataCollector;

use Symfinity\UiKernel\Theme\ActiveThemeContext;
use Symfinity\UiKernel\Theme\Theme;
use Symfinity\UiKernel\Theme\ThemePreferenceResolver;
use Symfinity\UiKernel\Theme\ThemeRegistry;
use Symfinity\UiKernel\Token\ThemeTokenSchema;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UiKernelDataCollector extends DataCollector
{
    public const CSS_BYTES_REQUEST_ATTR = '_symfinity_ui_kernel_css_bytes';

    /** @var list<string> */
    private const SHOWCASE_ROUTES = ['ui_kernel_showcase', 'ux_blocks_demo_kernel'];

    public function __construct(
        private readonly ActiveThemeContext $activeThemeContext,
        private readonly ThemePreferenceResolver $resolver,
        private readonly ThemeRegistry $themeRegistry,
        private readonly UrlGeneratorInterface $router,
    ) {
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        $path = $request->getPathInfo();
        if (str_starts_with($path, '/_wdt') || str_starts_with($path, '/_profiler')) {
            $this->data = ['enabled' => true];

            return;
        }

        $preference = $this->activeThemeContext->preferenceFromRequest($request);
        $systemPrefersDark = $this->resolver->resolveSystemPrefersDark($request);
        $themeId = $this->activeThemeContext->resolvedThemeIdFromRequest($request);
        $activeTheme = $this->activeThemeContext->activeThemeFromRequest($request);

        /** @var list<array{id: string, label: string}> $themes */
        $themes = [];
        foreach ($this->themeRegistry->all() as $theme) {
            $themes[] = [
                'id' => $theme->id(),
                'label' => $theme->label(),
            ];
        }

        usort(
            $themes,
            static fn (array $a, array $b): int => $a['id'] <=> $b['id'],
        );

        $this->data = [
            'enabled' => true,
            'themeId' => $themeId,
            'lineage' => $preference->lineage,
            'scheme' => $preference->scheme->value,
            'systemPrefersDark' => $systemPrefersDark,
            'cssBytes' => (int) $request->attributes->get(self::CSS_BYTES_REQUEST_ATTR, 0),
            'tokenCount' => \count($activeTheme->tokens()->all()),
            'activeTheme' => [
                'id' => $activeTheme->id(),
                'label' => $activeTheme->label(),
                'schemaVersion' => $activeTheme->schemaVersion(),
                'scrollMotion' => $activeTheme->scrollMotion(),
            ],
            'themes' => $themes,
            'themeCount' => \count($themes),
            'colorPalette' => $this->buildColorPalette($activeTheme),
            'showcaseUrl' => $this->resolveShowcaseUrl(),
        ];
    }

    public function reset(): void
    {
        $this->data = [];
    }

    public function getName(): string
    {
        return 'ui_kernel';
    }

    public function isEnabled(): bool
    {
        return (bool) ($this->data['enabled'] ?? false);
    }

    public function getThemeId(): ?string
    {
        $themeId = $this->data['themeId'] ?? null;

        return is_string($themeId) ? $themeId : null;
    }

    public function getLineage(): string
    {
        return (string) ($this->data['lineage'] ?? '');
    }

    public function getScheme(): string
    {
        return (string) ($this->data['scheme'] ?? '');
    }

    public function isSystemPrefersDark(): bool
    {
        return (bool) ($this->data['systemPrefersDark'] ?? false);
    }

    public function getCssBytes(): int
    {
        return (int) ($this->data['cssBytes'] ?? 0);
    }

    public function getThemeCount(): int
    {
        return (int) ($this->data['themeCount'] ?? 0);
    }

    public function getTokenCount(): int
    {
        return (int) ($this->data['tokenCount'] ?? 0);
    }

    /**
     * @return array{id: string, label: string, schemaVersion: string, scrollMotion: bool}
     */
    public function getActiveTheme(): array
    {
        $activeTheme = $this->data['activeTheme'] ?? [];

        return [
            'id' => (string) ($activeTheme['id'] ?? ''),
            'label' => (string) ($activeTheme['label'] ?? ''),
            'schemaVersion' => (string) ($activeTheme['schemaVersion'] ?? ''),
            'scrollMotion' => (bool) ($activeTheme['scrollMotion'] ?? false),
        ];
    }

    /**
     * @return list<array{id: string, label: string}>
     */
    public function getThemes(): array
    {
        $themes = $this->data['themes'] ?? [];
        if (!\is_array($themes)) {
            return [];
        }

        $normalized = [];
        foreach ($themes as $theme) {
            if (!\is_array($theme)) {
                continue;
            }

            $normalized[] = [
                'id' => (string) ($theme['id'] ?? ''),
                'label' => (string) ($theme['label'] ?? ''),
            ];
        }

        return $normalized;
    }

    /**
     * @return list<array{cssVar: string, value: string, shortName: string}>
     */
    public function getColorPalette(): array
    {
        $palette = $this->data['colorPalette'] ?? [];
        if (!\is_array($palette)) {
            return [];
        }

        $normalized = [];
        foreach ($palette as $entry) {
            if (!\is_array($entry)) {
                continue;
            }

            $normalized[] = [
                'cssVar' => (string) ($entry['cssVar'] ?? ''),
                'value' => (string) ($entry['value'] ?? ''),
                'shortName' => (string) ($entry['shortName'] ?? ''),
            ];
        }

        return $normalized;
    }

    public function getShowcaseUrl(): ?string
    {
        $url = $this->data['showcaseUrl'] ?? null;

        return is_string($url) ? $url : null;
    }

    /**
     * @return list<array{cssVar: string, value: string, shortName: string}>
     */
    private function buildColorPalette(Theme $activeTheme): array
    {
        $tokens = $activeTheme->tokens()->all();
        $palette = [];

        foreach (ThemeTokenSchema::COLOR_KEYS as $cssVar) {
            $palette[] = [
                'cssVar' => $cssVar,
                'value' => (string) ($tokens[$cssVar] ?? ''),
                'shortName' => str_starts_with($cssVar, '--ui-color-')
                    ? substr($cssVar, \strlen('--ui-color-'))
                    : $cssVar,
            ];
        }

        return $palette;
    }

    private function resolveShowcaseUrl(): ?string
    {
        foreach (self::SHOWCASE_ROUTES as $routeName) {
            try {
                return $this->router->generate($routeName);
            } catch (RouteNotFoundException) {
                continue;
            }
        }

        return null;
    }
}
