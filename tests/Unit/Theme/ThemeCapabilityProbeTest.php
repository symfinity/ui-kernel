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
