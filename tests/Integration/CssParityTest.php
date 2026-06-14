<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Integration;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Dtcg\DesignSystemLayerRegistry;
use Symfinity\UiKernel\Dtcg\LayerStackBuilder;
use Symfinity\UiKernel\Dtcg\LayeredTokenResolver;
use Symfinity\UiKernel\Dtcg\ThemeDtcgResolver;
use Symfinity\UiKernel\Theme\Theme;
use Symfinity\UiKernel\Theme\ThemeCatalog;

/**
 * Output parity oracle (076 US1 / 077 SC-001): built-in themes MUST match committed baselines
 * via the single DTCG resolution path.
 *
 * Variant inventory (T003): `default`, `default-dark`, `semantic`, `semantic-dark`,
 * `utility`, `utility-dark` — fixtures under `tests/Integration/parity/{id}.json`.
 */
final class CssParityTest extends TestCase
{
    #[Test]
    public function everyBuiltinThemeEmitsEquivalentCssThroughTheDtcgCore(): void
    {
        $generator = new CssGenerator();
        $dtcgResolver = new ThemeDtcgResolver(new LayerStackBuilder(
            new DesignSystemLayerRegistry(DesignSystemLayerRegistry::defaultDirectory()),
        ));
        $layerResolver = new LayeredTokenResolver();

        $themes = ThemeCatalog::all();
        self::assertNotEmpty($themes, 'expected built-in themes to compare');

        foreach ($themes as $theme) {
            \assert($theme instanceof Theme);

            $baseline = $generator->forTheme($theme);

            $variant = ThemeCatalog::variant($theme->id());
            $tokenSet = $dtcgResolver->resolve($variant);
            $dtcg = $generator->forResolvedTokens(
                $theme->id(),
                $tokenSet,
                $theme->schemaVersion(),
                scrollMotion: $theme->scrollMotion(),
            );

            self::assertSame(
                self::sortedMap(self::variableMap($baseline)),
                self::sortedMap(self::variableMap($dtcg)),
                $theme->id() . ' --ui-* variable parity',
            );

            self::assertSame($baseline, $dtcg, $theme->id() . ' full CSS parity');

            self::assertSame(
                self::sortedMap(self::baselineFixture($theme->id())),
                self::sortedMap(self::variableMap($dtcg)),
                $theme->id() . ' DTCG output matches committed baseline',
            );
        }
    }

    /**
     * @return array<string, string>
     */
    private static function baselineFixture(string $themeId): array
    {
        $path = __DIR__ . '/parity/' . $themeId . '.json';
        self::assertFileExists($path, 'missing parity baseline for ' . $themeId);

        $contents = file_get_contents($path);
        self::assertIsString($contents);

        /** @var array<string, string> $decoded */
        $decoded = json_decode($contents, true, 512, \JSON_THROW_ON_ERROR);

        return $decoded;
    }

    /**
     * @return array<string, string> ordered `--ui-*: value` declarations
     */
    private static function variableMap(string $css): array
    {
        preg_match_all('/(--ui-[\w-]+):\s*([^;]+);/', $css, $matches, \PREG_SET_ORDER);

        $map = [];
        foreach ($matches as $match) {
            $map[$match[1]] = trim($match[2]);
        }

        return $map;
    }

    /**
     * @param array<string, string> $map
     *
     * @return array<string, string>
     */
    private static function sortedMap(array $map): array
    {
        ksort($map);

        return $map;
    }
}
