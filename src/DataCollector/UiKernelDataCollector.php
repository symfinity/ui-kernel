<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\DataCollector;

use Symfinity\UiKernel\Contract\Catalog\GraphVariantCatalogPort;
use Symfinity\UiKernel\Theme\ActiveThemeContext;
use Symfinity\UiKernel\Theme\ThemePreferenceResolver;
use Symfinity\UiKernel\Theme\ThemeRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UiKernelDataCollector extends DataCollector
{
    public const CSS_BYTES_REQUEST_ATTR = '_symfinity_ui_kernel_css_bytes';

    /**
     * Best-effort deep links to a kernel/demo gallery if the host app happens to
     * expose one. The slim kernel does not own these routes; they resolve to null
     * when absent (see resolveShowcaseUrl), so this is a convenience, not a dependency.
     *
     * @var list<string>
     */
    private const SHOWCASE_ROUTES = ['ui_kernel_showcase', 'ux_blocks_demo_kernel'];

    public function __construct(
        private readonly ActiveThemeContext $activeThemeContext,
        private readonly ThemePreferenceResolver $resolver,
        private readonly ThemeRegistry $themeRegistry,
        private readonly UrlGeneratorInterface $router,
        private readonly ?GraphVariantCatalogPort $variantCatalog = null,
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

        $this->data = [
            'enabled' => true,
            'themeId' => $this->activeThemeContext->resolvedThemeIdFromRequest($request),
            'lineage' => $preference->lineage,
            'scheme' => $preference->scheme->value,
            'systemPrefersDark' => $this->resolver->resolveSystemPrefersDark($request),
            'cssBytes' => (int) $request->attributes->get(self::CSS_BYTES_REQUEST_ATTR, 0),
            'themeCount' => \count($this->themeRegistry->all()),
            'semantic_color_slugs' => $this->variantCatalog?->semanticColorSlugs() ?? [],
            'graph_layer_signature' => $this->variantCatalog?->layerSignature() ?? '',
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

    public function getShowcaseUrl(): ?string
    {
        $url = $this->data['showcaseUrl'] ?? null;

        return is_string($url) ? $url : null;
    }

    /**
     * @return list<string>
     */
    public function getSemanticColorSlugs(): array
    {
        $slugs = $this->data['semantic_color_slugs'] ?? [];
        if (!\is_array($slugs)) {
            return [];
        }

        return array_values(array_filter($slugs, static fn (mixed $slug): bool => \is_string($slug) && $slug !== ''));
    }

    public function getGraphLayerSignature(): string
    {
        return (string) ($this->data['graph_layer_signature'] ?? '');
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
