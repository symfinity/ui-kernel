<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Theme;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfinity\UiKernel\Contract\Token\TokenInterface;
use Symfinity\UiKernel\Dtcg\BuiltinDtcgThemeCatalog;
use Symfinity\UiKernel\Dtcg\BuiltinThemeVariant;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Theme\ThemeInheritanceCycleException;
use Symfinity\UiKernel\Theme\ThemeInheritanceDepthException;
use Symfinity\UiKernel\Theme\ThemeInheritanceGraph;
use Symfinity\UiKernel\Theme\ThemeInheritanceMerger;
use Symfinity\UiKernel\Theme\ThemeInheritanceUnknownParentException;

final class ThemeInheritanceChainTest extends TestCase
{
    protected function tearDown(): void
    {
        BuiltinDtcgThemeCatalog::reset();
        ThemeCatalog::reset();
        parent::tearDown();
    }

    #[Test]
    public function threeHopChainAppliesOverridesAtEachLevel(): void
    {
        $variants = [
            $this->variant('hop-c', extends: 'hop-b'),
            $this->variant('hop-b', extends: 'hop-a'),
            $this->variant('hop-a'),
        ];

        $graph = new ThemeInheritanceGraph($variants);
        $merger = new ThemeInheritanceMerger($graph);

        $tokens = $merger->mergedThemeTokens($variants[0]);

        self::assertSame('red.500', $this->tokenStringValue($tokens, 'color.primary'));
        self::assertSame('blue.500', $this->tokenStringValue($tokens, 'color.accent'));
        self::assertSame('green.500', $this->tokenStringValue($tokens, 'color.success'));
    }

    #[Test]
    public function cycleRaisesCycleException(): void
    {
        $this->expectException(ThemeInheritanceCycleException::class);

        new ThemeInheritanceGraph([
            $this->variant('a', extends: 'b'),
            $this->variant('b', extends: 'a'),
        ]);
    }

    #[Test]
    public function depthOverflowRaisesDepthException(): void
    {
        $variants = [];
        for ($i = 0; $i <= 9; ++$i) {
            $id = 'v' . $i;
            $parent = $i === 0 ? null : 'v' . ($i - 1);
            $variants[] = $this->variant($id, extends: $parent);
        }

        $this->expectException(ThemeInheritanceDepthException::class);

        new ThemeInheritanceGraph($variants, maxDepth: 8);
    }

    #[Test]
    public function unknownParentRaisesAtGraphBuild(): void
    {
        $this->expectException(ThemeInheritanceUnknownParentException::class);

        new ThemeInheritanceGraph([
            $this->variant('child', extends: 'missing-parent'),
        ]);
    }

    #[Test]
    public function appFixtureChainBuildsViaCatalog(): void
    {
        $roots = $this->tempThemeRoots();
        $this->writeChainLineage($roots['app'] . '/chain', ['chain-a', 'chain-b', 'chain-c']);

        $catalog = new BuiltinDtcgThemeCatalog($roots['bundle'], $roots['app'], new NullLogger());
        $graph = ThemeInheritanceGraph::fromCatalog($catalog);

        self::assertSame(['chain-a', 'chain-b', 'chain-c'], $graph->resolveChain('chain-c'));
        self::assertTrue($graph->hasNestedTheme());
        self::assertTrue($graph->hasInheritanceChain());
    }

    private function variant(string $id, ?string $extends = null): BuiltinThemeVariant
    {
        $dir = sys_get_temp_dir() . '/ui-kernel-chain-' . $id . '-' . uniqid('', true);
        mkdir($dir, 0755, true);

        $layerFile = $id . '.dtcg.yaml';
        $yaml = match ($id) {
            'hop-a', 'v0', 'chain-a' => "color:\n  primary:\n    \$value: 'red.500'\n    \$type: color\n",
            'hop-b', 'chain-b' => "color:\n  accent:\n    \$value: 'blue.500'\n    \$type: color\n",
            default => "color:\n  success:\n    \$value: 'green.500'\n    \$type: color\n",
        };
        file_put_contents($dir . '/' . $layerFile, $yaml);

        return new BuiltinThemeVariant(
            $id,
            $id,
            'fixture',
            'symfinity',
            \Symfinity\UiKernel\Theme\LayoutProfile::Semantic,
            \Symfinity\UiKernel\Token\MonoTone::from('slate'),
            $dir . '/' . $layerFile,
            $this->fixturePaletteDefinition(),
            extends: $extends,
        );
    }

    /**
     * @return array{
     *     hue_base: array<string, float>,
     *     mono_tones: array<string, array{hue: float, saturation: float}>,
     *     hue_chroma: array<string, float>,
     *     scale_anchors: array<string, string>
     * }
     */
    private function fixturePaletteDefinition(): array
    {
        return [
            'hue_base' => ['blue' => 250.0],
            'mono_tones' => ['slate' => ['hue' => 240.0, 'saturation' => 7.5]],
            'hue_chroma' => ['blue' => 0.12],
            'scale_anchors' => [],
        ];
    }

    /**
     * @param array<string, TokenInterface> $tokens
     */
    private function tokenStringValue(array $tokens, string $path): string
    {
        $token = $tokens[$path] ?? null;
        self::assertInstanceOf(TokenInterface::class, $token);
        $value = $token->value();
        self::assertIsString($value);

        return $value;
    }

    /**
     * @param list<string> $ids root → leaf
     */
    private function writeChainLineage(string $dir, array $ids): void
    {
        $lineage = basename($dir);
        mkdir($dir, 0755, true);

        $variantsYaml = '';
        foreach ($ids as $index => $id) {
            $extends = $index === 0 ? '' : "\n    extends: {$ids[$index - 1]}";
            $variantsYaml .= <<<YAML

  - id: {$id}
    layer_file: {$id}.dtcg.yaml
    label: {$id}
    tone: slate
    mode: light{$extends}
YAML;
            file_put_contents($dir . '/' . $id . '.dtcg.yaml', "color:\n  role_{$id}:\n    \$value: 'slate.500'\n    \$type: color\n");
        }

        file_put_contents($dir . '/theme.meta.yaml', <<<YAML
lineage: {$lineage}
design_system_id: symfinity
palette:
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
variants:{$variantsYaml}
YAML);
    }

    /**
     * @return array{app: string, bundle: string}
     */
    private function tempThemeRoots(): array
    {
        $base = sys_get_temp_dir() . '/ui-kernel-chain-catalog-' . uniqid('', true);
        $app = $base . '/app';
        $bundle = $base . '/bundle';
        mkdir($app, 0755, true);
        mkdir($bundle, 0755, true);

        foreach (['default', 'semantic', 'utility'] as $lineage) {
            $src = BuiltinDtcgThemeCatalog::defaultDirectory() . '/' . $lineage;
            $dest = $bundle . '/' . $lineage;
            $this->copyDir($src, $dest);
        }

        return ['app' => $app, 'bundle' => $bundle];
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
}
