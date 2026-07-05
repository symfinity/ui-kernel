<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Theme;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfinity\UiKernel\Contract\Layer\LayerRole;
use Symfinity\UiKernel\Contract\Token\AliasReference;
use Symfinity\UiKernel\Contract\Token\TokenInterface;
use Symfinity\UiKernel\Dtcg\BuiltinDtcgThemeCatalog;
use Symfinity\UiKernel\Dtcg\DesignSystemLayerRegistry;
use Symfinity\UiKernel\Dtcg\LayerStackBuilder;
use Symfinity\UiKernel\Dtcg\LayeredTokenResolver;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Theme\ThemeInheritanceGraph;
use Symfinity\UiKernel\Theme\ThemeInheritanceMerger;

final class NestedThemeResolutionTest extends TestCase
{
    private const FIXTURE_PALETTE = <<<'YAML'
  mono_saturation: 7.5
  hues:
    red: 25
    orange: 50
    amber: 75
    yellow: 100
    lime: 115
    green: 145
    emerald: 165
    teal: 185
    cyan: 205
    sky: 225
    blue: 250
    indigo: 270
    violet: 290
    purple: 310
    fuchsia: 330
    pink: 345
    rose: 15
YAML;

    protected function tearDown(): void
    {
        BuiltinDtcgThemeCatalog::reset();
        ThemeCatalog::reset();
        parent::tearDown();
    }

    #[Test]
    public function childOverrideWinsAndParentSurfaceInherited(): void
    {
        $roots = $this->tempThemeRoots();
        $this->writeChildLineage(
            $roots['app'] . '/brand-a',
            childId: 'brand-a',
            parentId: 'semantic',
            primaryRef: 'red.600',
        );

        $catalog = new BuiltinDtcgThemeCatalog($roots['bundle'], $roots['app'], new NullLogger());
        $graph = ThemeInheritanceGraph::fromCatalog($catalog);
        $merger = new ThemeInheritanceMerger($graph);
        $builder = new LayerStackBuilder(
            DesignSystemLayerRegistry::fromDefaultDirectory(),
            inheritanceMerger: $merger,
        );

        $child = $catalog->get('brand-a');
        $stack = $builder->forBuiltinVariant($child, $child->paletteRecipe());
        $merged = (new LayeredTokenResolver())->resolve($stack)->all();

        self::assertSame('red.600', $this->aliasTarget($merged, 'color.primary'));
        self::assertArrayHasKey('color.surface.base', $merged);
    }

    #[Test]
    public function childWithNoOverridesMatchesParentThemeLayer(): void
    {
        $roots = $this->tempThemeRoots();
        $this->writeChildLineage(
            $roots['app'] . '/brand-b',
            childId: 'brand-b',
            parentId: 'semantic',
            primaryRef: null,
        );

        $catalog = new BuiltinDtcgThemeCatalog($roots['bundle'], $roots['app'], new NullLogger());
        $graph = ThemeInheritanceGraph::fromCatalog($catalog);
        $merger = new ThemeInheritanceMerger($graph);
        $builder = new LayerStackBuilder(
            DesignSystemLayerRegistry::fromDefaultDirectory(),
            inheritanceMerger: $merger,
        );

        $parent = $catalog->get('semantic');
        $child = $catalog->get('brand-b');

        $parentTheme = $this->themeTokensOnly($builder, $parent);
        $childTheme = $this->themeTokensOnly($builder, $child);

        self::assertSame($parentTheme, $childTheme);
    }

    /**
     * @param array<string, TokenInterface> $merged
     */
    private function aliasTarget(array $merged, string $path): string
    {
        $token = $merged[$path] ?? null;
        self::assertInstanceOf(TokenInterface::class, $token);

        $value = $token->value();
        self::assertIsString($value);

        return trim($value, '{}');
    }

    /**
     * @return array<string, string>
     */
    private function themeTokensOnly(LayerStackBuilder $builder, \Symfinity\UiKernel\Dtcg\BuiltinThemeVariant $variant): array
    {
        $stack = $builder->forBuiltinVariant($variant, $variant->paletteRecipe());
        $themeLayer = null;
        foreach ($stack->ordered() as $layer) {
            if ($layer->role() === LayerRole::Theme) {
                $themeLayer = $layer;
            }
        }

        self::assertNotNull($themeLayer);

        $map = [];
        foreach ($themeLayer->tokens() as $path => $token) {
            self::assertInstanceOf(TokenInterface::class, $token);
            $map[$path] = $this->tokenMapValue($token);
        }

        return $map;
    }

    private function tokenMapValue(TokenInterface $token): string
    {
        $value = $token->value();
        if (is_string($value)) {
            return $value;
        }
        if ($value instanceof AliasReference) {
            return (string) $value;
        }

        self::fail(sprintf('Unexpected theme token value type: %s', get_debug_type($value)));
    }

    /**
     * @return array{app: string, bundle: string}
     */
    private function tempThemeRoots(): array
    {
        $base = sys_get_temp_dir() . '/ui-kernel-nested-' . uniqid('', true);
        $app = $base . '/app';
        $bundle = $base . '/bundle';
        mkdir($app, 0755, true);
        mkdir($bundle, 0755, true);

        foreach (['default', 'semantic', 'utility'] as $lineage) {
            $this->copyBundleLineage($lineage, $bundle);
        }

        return ['app' => $app, 'bundle' => $bundle];
    }

    private function writeChildLineage(
        string $dir,
        string $childId,
        string $parentId,
        ?string $primaryRef,
    ): void {
        $lineage = basename($dir);
        mkdir($dir, 0755, true);

        file_put_contents($dir . '/theme.meta.yaml', <<<YAML
lineage: {$lineage}
design_system_id: symfinity
palette:
{$this->fixturePalette()}
variants:
  - id: {$childId}
    layer_file: {$childId}.dtcg.yaml
    label: Child
    tone: slate
    mode: light
    extends: {$parentId}
YAML);

        if ($primaryRef !== null) {
            file_put_contents($dir . '/' . $childId . '.dtcg.yaml', <<<YAML
color:
  primary:
    \$value: '{$primaryRef}'
    \$type: color
YAML);
        } else {
            file_put_contents($dir . '/' . $childId . '.dtcg.yaml', "color: {}\n");
        }
    }

    private function copyBundleLineage(string $lineage, string $bundleRoot): void
    {
        $src = BuiltinDtcgThemeCatalog::defaultDirectory() . '/' . $lineage;
        $dest = $bundleRoot . '/' . $lineage;
        $this->copyDir($src, $dest);
    }

    private function copyDir(string $src, string $dest): void
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        foreach (scandir($src) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $from = $src . '/' . $entry;
            $to = $dest . '/' . $entry;
            if (is_dir($from)) {
                $this->copyDir($from, $to);
            } else {
                copy($from, $to);
            }
        }
    }

    private function fixturePalette(): string
    {
        return self::FIXTURE_PALETTE;
    }
}
