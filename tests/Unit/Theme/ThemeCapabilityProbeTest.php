<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Theme;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfinity\UiKernel\Dtcg\BuiltinDtcgThemeCatalog;
use Symfinity\UiKernel\Preview\PreviewHostContext;
use Symfinity\UiKernel\Preview\SessionThemeInjectionPort;
use Symfinity\UiKernel\Theme\ThemeCapabilityId;
use Symfinity\UiKernel\Theme\ThemeCapabilityProbe;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Theme\ThemeRegistry;
use Symfinity\UiKernel\Token\UserTokenSet;

final class ThemeCapabilityProbeTest extends TestCase
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
    public function enumCasesMatchHandbookIds(): void
    {
        self::assertSame(
            [
                'runtime_switch',
                'user_token_overlay',
                'session_preview_overlay',
                'app_dtcg_override',
                'nested_theme',
                'inheritance_chain',
            ],
            array_map(
                static fn (ThemeCapabilityId $id): string => $id->value,
                ThemeCapabilityId::cases(),
            ),
        );
    }

    #[Test]
    public function stockKernelEnablesRuntimeSwitchOnly(): void
    {
        $probe = $this->probe();

        self::assertTrue($probe->isEnabled(ThemeCapabilityId::RuntimeSwitch));
        self::assertFalse($probe->isEnabled(ThemeCapabilityId::UserTokenOverlay));
        self::assertFalse($probe->isEnabled(ThemeCapabilityId::SessionPreviewOverlay));
        self::assertFalse($probe->isEnabled(ThemeCapabilityId::AppDtcgOverride));
        self::assertSame(['runtime_switch'], $probe->enabledIds());
    }

    #[Test]
    public function userTokenOverlayEnabledWhenConfigNonEmpty(): void
    {
        $probe = $this->probe(
            userTokens: new UserTokenSet(['--ui-color-primary' => 'oklch(0.5 0.1 250)']),
        );

        self::assertTrue($probe->isEnabled(ThemeCapabilityId::UserTokenOverlay));
        self::assertContains('user_token_overlay', $probe->enabledIds());
    }

    #[Test]
    public function sessionPreviewOverlayEnabledWhenPortPresent(): void
    {
        $probe = $this->probe(sessionPort: new FakeSessionThemeInjectionPort());

        self::assertTrue($probe->isEnabled(ThemeCapabilityId::SessionPreviewOverlay));
        self::assertContains('session_preview_overlay', $probe->enabledIds());
    }

    #[Test]
    public function appDtcgOverrideEnabledWhenAppLineageMerged(): void
    {
        $roots = $this->tempThemeRoots();
        $this->copyDir(
            BuiltinDtcgThemeCatalog::defaultDirectory() . '/semantic',
            $roots['app'] . '/semantic',
        );

        $catalog = new BuiltinDtcgThemeCatalog($roots['bundle'], $roots['app'], new NullLogger());
        $registry = new ThemeRegistry($catalog);
        $probe = new ThemeCapabilityProbe(new UserTokenSet(), $registry);

        self::assertTrue($registry->hasAppLineages());
        self::assertTrue($probe->isEnabled(ThemeCapabilityId::AppDtcgOverride));
        self::assertContains('app_dtcg_override', $probe->enabledIds());
    }

    #[Test]
    public function enabledReturnsIdsInHandbookOrder(): void
    {
        $roots = $this->tempThemeRoots();
        $this->copyDir(
            BuiltinDtcgThemeCatalog::defaultDirectory() . '/semantic',
            $roots['app'] . '/semantic',
        );

        $catalog = new BuiltinDtcgThemeCatalog($roots['bundle'], $roots['app'], new NullLogger());
        $registry = new ThemeRegistry($catalog);
        $probe = new ThemeCapabilityProbe(
            new UserTokenSet(['--ui-color-primary' => 'oklch(0.5 0.1 250)']),
            $registry,
            new FakeSessionThemeInjectionPort(),
        );

        self::assertSame(
            [
                'runtime_switch',
                'user_token_overlay',
                'session_preview_overlay',
                'app_dtcg_override',
            ],
            $probe->enabledIds(),
        );
    }

    #[Test]
    public function nestedThemeEnabledWhenVariantExtendsParent(): void
    {
        $roots = $this->tempThemeRoots();
        $this->writeNestedChildLineage($roots['app'] . '/brand', 'brand-child', 'semantic');

        $catalog = new BuiltinDtcgThemeCatalog($roots['bundle'], $roots['app'], new NullLogger());
        $registry = new ThemeRegistry($catalog);
        $probe = new ThemeCapabilityProbe(new UserTokenSet(), $registry);

        self::assertTrue($probe->isEnabled(ThemeCapabilityId::NestedTheme));
        self::assertFalse($probe->isEnabled(ThemeCapabilityId::InheritanceChain));
    }

    #[Test]
    public function inheritanceChainEnabledForMultiHopCatalog(): void
    {
        $roots = $this->tempThemeRoots();
        $this->writeInheritanceChainLineage($roots['app'] . '/chain');

        $catalog = new BuiltinDtcgThemeCatalog($roots['bundle'], $roots['app'], new NullLogger());
        $registry = new ThemeRegistry($catalog);
        $probe = new ThemeCapabilityProbe(new UserTokenSet(), $registry);

        self::assertTrue($probe->isEnabled(ThemeCapabilityId::NestedTheme));
        self::assertTrue($probe->isEnabled(ThemeCapabilityId::InheritanceChain));
    }

    private function writeNestedChildLineage(string $dir, string $childId, string $parentId): void
    {
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
        copy(
            BuiltinDtcgThemeCatalog::defaultDirectory() . '/semantic/semantic.dtcg.yaml',
            $dir . '/' . $childId . '.dtcg.yaml',
        );
    }

    private function writeInheritanceChainLineage(string $dir): void
    {
        $lineage = basename($dir);
        mkdir($dir, 0755, true);
        file_put_contents($dir . '/theme.meta.yaml', <<<YAML
lineage: {$lineage}
design_system_id: symfinity
palette:
{$this->fixturePalette()}
variants:
  - id: chain-a
    layer_file: chain-a.dtcg.yaml
    label: A
    tone: slate
    mode: light
  - id: chain-b
    layer_file: chain-b.dtcg.yaml
    label: B
    tone: slate
    mode: light
    extends: chain-a
  - id: chain-c
    layer_file: chain-c.dtcg.yaml
    label: C
    tone: slate
    mode: light
    extends: chain-b
YAML);
        foreach (['chain-a', 'chain-b', 'chain-c'] as $id) {
            copy(
                BuiltinDtcgThemeCatalog::defaultDirectory() . '/semantic/semantic.dtcg.yaml',
                $dir . '/' . $id . '.dtcg.yaml',
            );
        }
    }

    private function fixturePalette(): string
    {
        return self::FIXTURE_PALETTE;
    }

    private function probe(
        ?UserTokenSet $userTokens = null,
        ?ThemeRegistry $registry = null,
        ?SessionThemeInjectionPort $sessionPort = null,
    ): ThemeCapabilityProbe {
        return new ThemeCapabilityProbe(
            $userTokens ?? new UserTokenSet(),
            $registry ?? new ThemeRegistry(),
            $sessionPort,
        );
    }

    /**
     * @return array{app: string, bundle: string}
     */
    private function tempThemeRoots(): array
    {
        $base = sys_get_temp_dir() . '/ui-kernel-probe-' . uniqid('', true);
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
}

final class FakeSessionThemeInjectionPort implements SessionThemeInjectionPort
{
    public function resolve(string $token, ?string $polarity = null): ?PreviewHostContext
    {
        return null;
    }

    public function revoke(string $token): void
    {
    }
}
