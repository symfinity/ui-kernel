<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Dtcg;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfinity\UiKernel\Dtcg\BuiltinDtcgThemeCatalog;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Theme\ThemeRegistry;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class MergedThemeCatalogTest extends TestCase
{
    protected function tearDown(): void
    {
        BuiltinDtcgThemeCatalog::reset();
        ThemeCatalog::reset();
        parent::tearDown();
    }

    #[Test]
    public function appOnlyLineageRegisters(): void
    {
        $root = $this->tempThemeRoots();
        $this->writeMinimalLineage($root['app'] . '/fixture-lineage', 'fixture-lineage', 'fixture-light', 'fixture-light.dtcg.yaml');

        $catalog = new BuiltinDtcgThemeCatalog($root['bundle'], $root['app'], new NullLogger());
        $ids = array_map(static fn ($v) => $v->id(), $catalog->all());

        self::assertContains('fixture-light', $ids);
        self::assertSame('app', $catalog->get('fixture-light')->catalogSource());
    }

    #[Test]
    public function appLineageOverridesBundleSemantic(): void
    {
        $root = $this->tempThemeRoots();
        $this->writeMinimalLineage($root['app'] . '/semantic', 'semantic', 'semantic', 'semantic.dtcg.yaml', mode: 'light');
        $this->writeMinimalLayer($root['app'] . '/semantic/semantic.dtcg.yaml', 'primary', 'blue.600');

        $catalog = new BuiltinDtcgThemeCatalog($root['bundle'], $root['app'], new NullLogger());
        $ids = array_map(static fn ($v) => $v->id(), $catalog->all());

        self::assertContains('semantic', $ids);
        self::assertNotContains('semantic-dark', $ids);
        self::assertSame('app', $catalog->get('semantic')->catalogSource());
    }

    #[Test]
    public function invalidAppLineageSkippedWithWarning(): void
    {
        $root = $this->tempThemeRoots();
        mkdir($root['app'] . '/broken', 0755, true);
        file_put_contents($root['app'] . '/broken/theme.meta.yaml', "lineage: broken\nvariants: []\n");

        $catalog = new BuiltinDtcgThemeCatalog($root['bundle'], $root['app'], new NullLogger());
        $ids = array_map(static fn ($v) => $v->id(), $catalog->all());

        self::assertContains('default', $ids);
        self::assertNotContains('broken', $ids);
    }

    #[Test]
    public function registryCssUnchangedWhenAppTreeEmpty(): void
    {
        BuiltinDtcgThemeCatalog::reset();
        ThemeCatalog::reset();

        $baseline = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V2_0);

        $emptyApp = sys_get_temp_dir() . '/ui-kernel-empty-app-' . uniqid('', true);
        mkdir($emptyApp, 0755, true);

        $catalog = new BuiltinDtcgThemeCatalog(BuiltinDtcgThemeCatalog::defaultDirectory(), $emptyApp);
        ThemeCatalog::bindDtcgCatalog($catalog);
        $registry = new ThemeRegistry($catalog);

        $css = (new CssGenerator())->forTheme($registry->get('semantic'), ThemeTokenSchema::V2_0);
        self::assertSame($baseline, $css);
    }

    /**
     * @return array{app: string, bundle: string}
     */
    private function tempThemeRoots(): array
    {
        $base = sys_get_temp_dir() . '/ui-kernel-merge-' . uniqid('', true);
        $app = $base . '/app';
        $bundle = $base . '/bundle';
        mkdir($app, 0755, true);
        mkdir($bundle, 0755, true);

        foreach (['default', 'semantic', 'utility'] as $lineage) {
            $this->copyBundleLineage($lineage, $bundle);
        }

        return ['app' => $app, 'bundle' => $bundle];
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

    private function writeMinimalLineage(
        string $dir,
        string $lineage,
        string $variantId,
        string $layerFile,
        string $mode = 'light',
    ): void {
        mkdir($dir, 0755, true);
        file_put_contents($dir . '/theme.meta.yaml', <<<YAML
lineage: {$lineage}
design_system_id: chameleon
palette:
  mono_saturation: 7.5
  hues:
    blue: 250
variants:
  - id: {$variantId}
    layer_file: {$layerFile}
    label: Fixture
    tone: slate
    mode: {$mode}
YAML);
        $this->writeMinimalLayer($dir . '/' . $layerFile);
    }

    private function writeMinimalLayer(string $path, string $role = 'primary', string $ref = 'slate.600'): void
    {
        file_put_contents($path, <<<YAML
color:
  {$role}:
    \$value: '{$ref}'
    \$type: color
YAML);
    }
}
