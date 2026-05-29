<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Flavour\FlavourCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class CssGeneratorTest extends TestCase
{
    #[Test]
    public function itSnapshotsTokenVariablesForTwoFlavours(): void
    {
        $generator = new CssGenerator();
        $dark = $generator->forFlavour(FlavourCatalog::get('dark'));
        $semantic = $generator->forFlavour(FlavourCatalog::get('semantic'));

        self::assertStringContainsString('[data-theme="dark"]', $dark);
        self::assertStringContainsString('[data-theme="semantic"]', $semantic);

        foreach (ThemeTokenSchema::REQUIRED_KEYS as $key) {
            self::assertStringContainsString($key, $dark);
            self::assertStringContainsString($key, $semantic);
        }
    }

    #[Test]
    public function itIncludesDangerAndSuccessButtonVariants(): void
    {
        $css = (new CssGenerator())->forFlavour(FlavourCatalog::get('default'));

        self::assertStringContainsString('[data-ui-role="button"][data-ui-variant="danger"]', $css);
        self::assertStringContainsString('[data-ui-role="button"][data-ui-variant="success"]', $css);
        self::assertStringContainsString('background: var(--ui-color-danger)', $css);
        self::assertStringContainsString('background: var(--ui-color-success)', $css);
    }
}
