<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CascadeLayerOrder;
use Symfinity\UiKernel\Css\CascadeLayerWrap;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;

final class CascadeLayersTest extends TestCase
{
    #[Test]
    public function platformLayerOrderListsAllTierSlots(): void
    {
        $declaration = CascadeLayerOrder::declaration();

        foreach (CascadeLayerOrder::PLATFORM_LAYERS as $layer) {
            self::assertStringContainsString($layer, $declaration, $layer);
        }

        self::assertStringStartsWith('@layer', $declaration);
    }

    #[Test]
    public function cssGeneratorWrapsTokensAndProfileLayers(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('default-dark'));

        self::assertStringContainsString('@layer kernel.tokens {', $css);
        self::assertStringContainsString('@layer kernel.profile {', $css);
        self::assertStringContainsString('[data-theme="default-dark"]', $css);
        self::assertStringContainsString('--ui-z-modal', $css);
    }

    #[Test]
    public function layerWrapSkipsDoubleWrap(): void
    {
        $inner = '[data-ui-role="button"] { padding: 1rem; }';
        $wrapped = CascadeLayerWrap::wrap(CascadeLayerOrder::BLOCKS_CORE, $inner);
        $again = CascadeLayerWrap::wrap(CascadeLayerOrder::BLOCKS_CORE, $wrapped);

        self::assertSame($wrapped, $again);
    }

    #[Test]
    public function unlayeredIntegratorRuleBeatsLayeredBlocksAtLowerSpecificity(): void
    {
        $css = CascadeLayerWrap::wrap(
            CascadeLayerOrder::BLOCKS_CORE,
            '[data-ui-role=button] { padding: 1rem; }',
        )."\n.proof-override [data-ui-role=button] { padding: 2rem; }";

        self::assertStringContainsString('@layer blocks.core {', $css);
        self::assertStringContainsString('.proof-override [data-ui-role=button]', $css);
        self::assertStringNotContainsString('@layer blocks.core { .proof-override', $css);
    }
}
