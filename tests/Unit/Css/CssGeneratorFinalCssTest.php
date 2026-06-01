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
use Symfinity\UiKernel\Flavour\FlavourCatalog;
use Symfinity\UiKernel\Token\FlavourThemeConfig;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class CssGeneratorFinalCssTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__ . '/../../fixtures/snapshots';

    #[Test]
    public function overlaySnapshotMatchesSemanticFixture(): void
    {
        $css = (new CssGenerator())->forFlavour(FlavourCatalog::get('semantic'));
        $this->assertMatchesSnapshotFixture('css-016-overlay-semantic.css', $css);
    }

    #[Test]
    public function overlaySnapshotMatchesUtilityFixture(): void
    {
        $css = (new CssGenerator())->forFlavour(FlavourCatalog::get('utility'));
        $this->assertMatchesSnapshotFixture('css-016-overlay-utility.css', $css);
    }

    #[Test]
    public function semanticOverlayTokensAndNativeSelectorsPresent(): void
    {
        $css = (new CssGenerator())->forFlavour(FlavourCatalog::get('semantic'));

        self::assertStringContainsString('--ui-overlay-surface:', $css);
        self::assertStringContainsString('--ui-backdrop-color:', $css);
        self::assertStringContainsString('dialog::backdrop', $css);
        self::assertStringContainsString('[popover]', $css);
        self::assertStringContainsString('[data-ui-role="modal"]', $css);
    }

    #[Test]
    public function overlayRulesUseProfileZIndexVariablesNotLiterals(): void
    {
        $css = (new CssGenerator())->forFlavour(FlavourCatalog::get('semantic'));

        self::assertStringContainsString('z-index: var(--ui-z-modal)', $css);
        self::assertStringContainsString('z-index: var(--ui-z-popover)', $css);
        self::assertDoesNotMatchRegularExpression('/z-index:\s*1050/', $css);
        self::assertDoesNotMatchRegularExpression('/z-index:\s*1060/', $css);
    }

    #[Test]
    public function utilityFlavourOmitsScrollTimeline(): void
    {
        $css = (new CssGenerator())->forFlavour(FlavourCatalog::get('utility'));

        self::assertStringNotContainsString('animation-timeline', $css);
    }

    #[Test]
    public function semanticFlavourWithScrollMotionIncludesViewTimeline(): void
    {
        $css = (new CssGenerator())->forFlavour(FlavourCatalog::get('semantic'));

        self::assertStringContainsString('animation-timeline: view()', $css);
        self::assertStringContainsString('[data-ui-scroll-reveal]', $css);
    }

    #[Test]
    public function scrollMotionFlagOnlyOnSemanticFlavours(): void
    {
        self::assertTrue(FlavourCatalog::get('semantic')->scrollMotion());
        self::assertTrue(FlavourCatalog::get('semantic-dark')->scrollMotion());
        self::assertFalse(FlavourCatalog::get('utility')->scrollMotion());
        self::assertFalse(FlavourCatalog::get('default')->scrollMotion());
    }

    #[Test]
    public function hasPatternsAndContentVisibilityPresent(): void
    {
        $css = (new CssGenerator())->forFlavour(FlavourCatalog::get('utility'));

        self::assertStringContainsString('[data-ui-role="field-group"]:has(:invalid)', $css);
        self::assertStringContainsString('[data-ui-defer="cv"]', $css);
        self::assertStringContainsString('content-visibility: auto', $css);
    }

    #[Test]
    public function anchorMenuRulesIncludeSupportsFallback(): void
    {
        $css = (new CssGenerator())->forFlavour(FlavourCatalog::get('semantic'));

        self::assertStringContainsString('anchor-name: --ui-menu-trigger', $css);
        self::assertStringContainsString('position-anchor: --ui-menu-trigger', $css);
        self::assertStringContainsString('@supports not (anchor-name: --ui-menu-trigger)', $css);
    }

    #[Test]
    public function reducedMotionDisablesSkeletonAnimation(): void
    {
        $css = (new CssGenerator())->forFlavour(FlavourCatalog::get('default'));

        self::assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        self::assertMatchesRegularExpression(
            '/@media \(prefers-reduced-motion: reduce\)[^{]*\{[^}]*\[data-ui-role="skeleton"\][^}]*animation:\s*none/s',
            $css,
        );
    }

    #[Test]
    public function flavourPhpDefinitionsDoNotEmbedZIndexLiterals(): void
    {
        $configPath = dirname(__DIR__, 3) . '/src/Token/FlavourThemeConfig.php';
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
    public function allFlavoursIncludeOverlayTokens(): void
    {
        foreach (['default', 'dark', 'semantic', 'semantic-dark', 'utility', 'utility-dark'] as $id) {
            $config = FlavourThemeConfig::get($id);
            $tokens = (new \Symfinity\UiKernel\Token\ThemeTokenResolver())->resolve($config)->all();
            foreach (ThemeTokenSchema::OVERLAY_KEYS_V2_ADDITIVE as $key) {
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
