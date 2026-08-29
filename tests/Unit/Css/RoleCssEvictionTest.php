<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Dtcg\BuiltinDtcgThemeCatalog;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

/** 078 — role CSS eviction gate (065 slim generator). */
final class RoleCssEvictionTest extends TestCase
{
    #[Test]
    public function generatedCssContainsNoDataUiRoleSelectors(): void
    {
        $generator = new CssGenerator();
        $catalog = BuiltinDtcgThemeCatalog::fromDefaultDirectory();

        foreach ($catalog->all() as $variant) {
            $css = $generator->forTheme(ThemeCatalog::get($variant->id()), ThemeTokenSchema::V1_0);
            self::assertDoesNotMatchRegularExpression('/\[data-ui-role/', $css, $variant->id());
        }
    }

    #[Test]
    public function cssGeneratorSourceContainsNoRoleSelectorEmitters(): void
    {
        $generatorSource = (string) file_get_contents(dirname(__DIR__, 3) . '/src/Css/CssGenerator.php');
        self::assertDoesNotMatchRegularExpression('/\[data-ui-role/', $generatorSource);
        self::assertStringNotContainsString('@keyframes', $generatorSource);
    }

    #[Test]
    public function roleRulesVersionRemainsTokensOnly(): void
    {
        self::assertSame('tokens-only:1', \Symfinity\UiKernel\Css\CssCacheKeyPolicy::roleRulesVersion());
    }
}
