<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

/** 078 — profile globals DTCG parity vs pre-migration oracle. */
final class ProfileGlobalsParityTest extends TestCase
{
    private const ORACLE = __DIR__ . '/fixtures/profile-globals-oracle.css';

    #[Test]
    public function profileGlobalsMatchOracleForBuiltInThemes(): void
    {
        $generator = new CssGenerator();
        $oracle = self::normalizeProfileGlobals((string) file_get_contents(self::ORACLE));

        foreach (ThemeCatalog::all() as $theme) {
            $css = $generator->forTheme($theme, ThemeTokenSchema::V1_0);
            $globals = self::extractProfileGlobals($css);

            self::assertSame($oracle, $globals, $theme->id());
        }
    }

    private static function extractProfileGlobals(string $css): string
    {
        $anchor = strpos($css, '--ui-z-dropdown:');
        self::assertNotFalse($anchor, 'profile globals z-index block missing');

        $start = strrpos(substr($css, 0, $anchor), ':root {');
        self::assertNotFalse($start);

        return self::normalizeProfileGlobals(substr($css, $start));
    }

    private static function normalizeProfileGlobals(string $block): string
    {
        $withoutReducedMotion = preg_replace(
            '/@media \\(prefers-reduced-motion: reduce\\) \\{.*\\}\\s*/s',
            '',
            $block,
        );

        return trim(preg_replace('/\\s+/', ' ', (string) $withoutReducedMotion) ?? '');
    }
}
