<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Dtcg;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Dtcg\BuiltinDtcgThemeCatalog;
use Symfinity\UiKernel\Dtcg\DesignSystemLayerRegistry;
use Symfinity\UiKernel\Dtcg\LayerStackBuilder;

final class LayerStackBuilderTest extends TestCase
{
    #[Test]
    public function stackOrdersBaseDesignSystemThenTheme(): void
    {
        $catalog = new BuiltinDtcgThemeCatalog(BuiltinDtcgThemeCatalog::defaultDirectory());
        $variant = $catalog->get('semantic');
        $stack = (new LayerStackBuilder(
            DesignSystemLayerRegistry::fromDefaultDirectory(),
        ))->forBuiltinVariant($variant, $variant->paletteRecipe());

        $layers = $stack->ordered();

        self::assertSame('base', $layers[0]->role()->value);
        self::assertSame('design_system', $layers[1]->role()->value);
        self::assertSame('theme', $layers[2]->role()->value);
    }
}
