<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;

/**
 * SC-002 (077): built-in theme path must not reference legacy bespoke resolver stack.
 */
final class NoLegacyThemeResolverTest extends TestCase
{
    /** @var list<string> */
    private const BUILTIN_PATH_MARKERS = [
        'Dtcg/BuiltinDtcgThemeCatalog.php',
        'Dtcg/ThemeDtcgResolver.php',
        'Dtcg/LayerStackBuilder.php',
        'Theme/ThemeCatalog.php',
        'Theme/DefinedTheme.php',
        'Css/CssGenerator.php',
    ];

    /** @var list<string> */
    private const FORBIDDEN_SYMBOLS = [
        'ThemeYamlNormalizer',
    ];

    #[Test]
    public function builtInRuntimePathDoesNotReferenceLegacyBespokeNormalizer(): void
    {
        $packageRoot = dirname(__DIR__, 2);
        $violations = [];

        foreach (self::BUILTIN_PATH_MARKERS as $relative) {
            $path = $packageRoot . '/src/' . $relative;
            self::assertFileExists($path);
            $source = file_get_contents($path);
            self::assertIsString($source);

            foreach (self::FORBIDDEN_SYMBOLS as $symbol) {
                if (str_contains($source, $symbol)) {
                    $violations[] = sprintf('%s references forbidden %s', $relative, $symbol);
                }
            }
        }

        self::assertSame([], $violations, implode("\n", $violations));
    }

    #[Test]
    public function semanticVariantEnumIsRemovedFromPackage(): void
    {
        $path = dirname(__DIR__, 2) . '/src/Token/SemanticVariant.php';
        self::assertFileDoesNotExist($path);
    }

    #[Test]
    public function themeTokenResolverIsAuthoringOnly(): void
    {
        $path = dirname(__DIR__, 2) . '/src/Token/ThemeTokenResolver.php';
        $source = file_get_contents($path);
        self::assertIsString($source);
        self::assertStringContainsString('AuthoringThemeConfig', $source);
        self::assertStringNotContainsString('BuiltinDtcgThemeCatalog', $source);
    }
}
