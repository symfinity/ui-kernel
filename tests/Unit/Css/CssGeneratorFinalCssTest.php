<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\ThemeConfig;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class CssGeneratorFinalCssTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__ . '/../../fixtures/snapshots';

    #[Test]
    public function overlaySnapshotMatchesSemanticFixture(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'));
        $this->assertMatchesSnapshotFixture('css-016-overlay-semantic.css', $css);
    }

    #[Test]
    public function overlaySnapshotMatchesUtilityFixture(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('utility'));
        $this->assertMatchesSnapshotFixture('css-016-overlay-utility.css', $css);
    }

    #[Test]
    public function overlaySnapshotMatchesBalancedFixture(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('default'));
        $this->assertMatchesSnapshotFixture('css-016-overlay-balanced.css', $css);
    }

    #[Test]
    public function semanticOverlayTokensPresentInKernelOutput(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'));

        self::assertStringContainsString('--ui-overlay-surface:', $css);
        self::assertStringContainsString('--ui-backdrop-color:', $css);
        self::assertStringNotContainsString('dialog::backdrop', $css);
        self::assertStringNotContainsString('[data-ui-role="modal"]', $css);
    }

    #[Test]
    public function kernelOutputDefinesZIndexTokensWithoutRoleRules(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'));

        self::assertStringContainsString('--ui-z-modal:', $css);
        self::assertStringNotContainsString('z-index: 1050', $css);
    }

    #[Test]
    public function utilityThemeOmitsScrollTimeline(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('utility'));

        self::assertStringNotContainsString('animation-timeline', $css);
    }

    #[Test]
    public function scrollMotionFlagOnlyOnSemanticThemes(): void
    {
        self::assertTrue(ThemeCatalog::get('semantic')->scrollMotion());
        self::assertTrue(ThemeCatalog::get('semantic-dark')->scrollMotion());
        self::assertFalse(ThemeCatalog::get('utility')->scrollMotion());
        self::assertFalse(ThemeCatalog::get('default')->scrollMotion());
    }

    #[Test]
    public function adaptiveThemePairEmitsPrefersColorSchemeBlock(): void
    {
        $css = (new CssGenerator())->forAdaptiveThemePair(
            ThemeCatalog::get('default'),
            ThemeCatalog::get('default-dark'),
        );

        self::assertStringContainsString('ui-kernel adaptive:default+default-dark', $css);
        self::assertStringContainsString('html[data-theme="default"]', $css);
        self::assertStringContainsString('@media (prefers-color-scheme: dark)', $css);
        self::assertStringContainsString('color-scheme: light dark', $css);
        self::assertStringNotContainsString('dialog::backdrop', $css);
        self::assertStringNotContainsString('[data-theme="default-dark"] {', $css);
    }

    #[Test]
    public function themePhpDefinitionsDoNotEmbedZIndexLiterals(): void
    {
        $configPath = dirname(__DIR__, 3) . '/src/Token/ThemeConfig.php';
        $source = file_get_contents($configPath);
        self::assertIsString($source);
        self::assertDoesNotMatchRegularExpression('/z-index:\s*\d+/', $source);
        self::assertDoesNotMatchRegularExpression('/--ui-z-\w+:\s*\d+/', $source);
    }

    #[Test]
    public function packageHasNoStimulusControllers(): void
    {
        $packageRoot = dirname(__DIR__, 3);
        $patterns = ['stimulus', 'assets/controllers'];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($packageRoot, \FilesystemIterator::SKIP_DOTS),
        );
        $regex = new RegexIterator($iterator, '/\.(php|twig|yaml|yml|json)$/i');

        /** @var SplFileInfo $file */
        foreach ($regex as $file) {
            $path = $file->getPathname();
            if (str_contains($path, '/vendor/') || str_contains($path, '/var/')) {
                continue;
            }
            $relative = str_replace($packageRoot . '/', '', $path);
            foreach ($patterns as $pattern) {
                self::assertStringNotContainsString(
                    $pattern,
                    $relative,
                    sprintf('SC-002: unexpected path segment "%s" in %s', $pattern, $relative),
                );
            }
        }
    }

    #[Test]
    public function allThemesIncludeOverlayTokens(): void
    {
        $resolver = new \Symfinity\UiKernel\Dtcg\ThemeDtcgResolver(
            new \Symfinity\UiKernel\Dtcg\LayerStackBuilder(
                new \Symfinity\UiKernel\Dtcg\DesignSystemLayerRegistry(
                    \Symfinity\UiKernel\Dtcg\DesignSystemLayerRegistry::defaultDirectory(),
                ),
            ),
        );

        foreach (['default', 'default-dark', 'semantic', 'semantic-dark', 'utility', 'utility-dark'] as $id) {
            $tokens = $resolver->resolve(ThemeCatalog::variant($id))->all();
            foreach (ThemeTokenSchema::OVERLAY_KEYS as $key) {
                self::assertArrayHasKey($key, $tokens, $id . ' missing ' . $key);
            }
        }
    }

    private function assertMatchesSnapshotFixture(string $filename, string $css): void
    {
        $path = self::FIXTURE_DIR . '/' . $filename;
        self::assertFileExists($path, 'Snapshot fixture missing: ' . $filename);
        $expected = file_get_contents($path);
        self::assertIsString($expected);
        self::assertSame(trim($expected), trim($css), $filename);
    }
}
