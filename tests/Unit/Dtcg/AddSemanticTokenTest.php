<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Dtcg;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Contract\Layer\LayerRole;
use Symfinity\UiKernel\Contract\Layer\LayerStack;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Dtcg\DtcgJsonReader;
use Symfinity\UiKernel\Dtcg\KernelTokenLayers;
use Symfinity\UiKernel\Dtcg\LayeredTokenResolver;
use Symfinity\UiKernel\Theme\ThemeCatalog;

/**
 * US2 (076 FR-006 / FR-007 / SC-002): a token-file-only addition surfaces a new `--ui-*`
 * and variant with zero change to kernel PHP source.
 */
final class AddSemanticTokenTest extends TestCase
{
    #[Test]
    public function addingColorAccentViaDesignSystemLayerEmitsVariableAndVariant(): void
    {
        $layers = new KernelTokenLayers();
        $base = $layers->baseLayerForTheme(ThemeCatalog::get('default'));

        $accentDoc = (new DtcgJsonReader())->fromString(<<<'JSON'
        {
          "color": {
            "accent": { "$type": "color", "$value": "{color.primary}" }
          }
        }
        JSON);

        $graph = (new LayeredTokenResolver())->resolve(
            new LayerStack($base, $accentDoc->asLayer('chameleon', LayerRole::DesignSystem)),
        );

        self::assertContains('accent', $graph->semanticColors());

        $css = (new CssGenerator())->forResolvedGraph($graph, 'default');
        self::assertStringContainsString('--ui-color-accent:', $css);
    }

    #[Test]
    public function addingNonColorTokenEmitsItsCustomProperty(): void
    {
        $layers = new KernelTokenLayers();
        $base = $layers->baseLayerForTheme(ThemeCatalog::get('default'));

        $spacingDoc = (new DtcgJsonReader())->fromString(<<<'JSON'
        {
          "space": {
            "fluid": { "$type": "dimension", "$value": "1rem" }
          }
        }
        JSON);

        $graph = (new LayeredTokenResolver())->resolve(
            new LayerStack($base, $spacingDoc->asLayer('chameleon', LayerRole::DesignSystem)),
        );

        $css = (new CssGenerator())->forResolvedGraph($graph, 'default');
        self::assertStringContainsString('--ui-space-fluid: 1rem;', $css);
    }
}
