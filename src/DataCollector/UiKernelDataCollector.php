<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\DataCollector;

use Symfinity\UiKernel\Contract\Catalog\GraphVariantCatalogPort;
use Symfinity\UiKernel\Dtcg\BuiltinDtcgThemeCatalog;
use Symfinity\UiKernel\Theme\ActiveThemeContext;
use Symfinity\UiKernel\Theme\EffectivePhysicsResolver;
use Symfinity\UiKernel\Theme\ThemePreferenceResolver;
use Symfinity\UiKernel\Theme\ThemeRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UiKernelDataCollector extends DataCollector
{    public const CSS_BYTES_REQUEST_ATTR = '_symfinity_ui_kernel_css_bytes';

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
        private readonly EffectivePhysicsResolver $physicsResolver,
        private readonly BuiltinDtcgThemeCatalog $themeCatalog,
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

        $cssBytesAttr = $request->attributes->get(self::CSS_BYTES_REQUEST_ATTR, 0);
        $cssBytes = is_int($cssBytesAttr) ? $cssBytesAttr : (is_numeric($cssBytesAttr) ? (int) $cssBytesAttr : 0);

        $themeId = $this->activeThemeContext->resolvedThemeIdFromRequest($request);
        $variant = $this->themeCatalog->get($themeId);
        $physicsResolution = $this->physicsResolver->resolve(
            $variant->physics(),
            $variant->isDarkVariant(),
        );

        $this->data = [
            'enabled' => true,
            'themeId' => $themeId,
            'lineage' => $preference->lineage,
            'scheme' => $preference->scheme->value,
            'systemPrefersDark' => $this->resolver->resolveSystemPrefersDark($request),
            'cssBytes' => $cssBytes,
            'themeCount' => \count($this->themeRegistry->all()),
            'semantic_color_slugs' => $this->variantCatalog?->semanticColorSlugs() ?? [],
            'graph_layer_signature' => $this->variantCatalog?->layerSignature() ?? '',
            'showcaseUrl' => $this->resolveShowcaseUrl(),
            'requestedPhysics' => $physicsResolution->requested->value,
            'effectivePhysics' => $physicsResolution->effective->value,
            'physicsCorrected' => $physicsResolution->corrected,
            'physicsCorrectionReason' => $physicsResolution->correctionReason,
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
        $lineage = $this->data['lineage'] ?? '';

        return is_string($lineage) ? $lineage : '';
    }

    public function getScheme(): string
    {
        $scheme = $this->data['scheme'] ?? '';

        return is_string($scheme) ? $scheme : '';
    }

    public function isSystemPrefersDark(): bool
    {
        return (bool) ($this->data['systemPrefersDark'] ?? false);
    }

    public function getCssBytes(): int
    {
        $cssBytes = $this->data['cssBytes'] ?? 0;

        return is_int($cssBytes) ? $cssBytes : (is_numeric($cssBytes) ? (int) $cssBytes : 0);
    }

    public function getThemeCount(): int
    {
        $themeCount = $this->data['themeCount'] ?? 0;

        return is_int($themeCount) ? $themeCount : (is_numeric($themeCount) ? (int) $themeCount : 0);
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
        $signature = $this->data['graph_layer_signature'] ?? '';

        return is_string($signature) ? $signature : '';
    }

    public function getRequestedPhysics(): ?string
    {
        $value = $this->data['requestedPhysics'] ?? null;

        return is_string($value) ? $value : null;
    }

    public function getEffectivePhysics(): ?string
    {
        $value = $this->data['effectivePhysics'] ?? null;

        return is_string($value) ? $value : null;
    }

    public function isPhysicsCorrected(): bool
    {
        return (bool) ($this->data['physicsCorrected'] ?? false);
    }

    public function getPhysicsCorrectionReason(): ?string
    {
        $value = $this->data['physicsCorrectionReason'] ?? null;

        return is_string($value) ? $value : null;
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
