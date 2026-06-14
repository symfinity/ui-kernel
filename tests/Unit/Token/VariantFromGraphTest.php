<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Dtcg\KernelTokenLayers;
use Symfinity\UiKernel\Dtcg\LayeredTokenResolver;
use Symfinity\UiKernel\Contract\Layer\LayerStack;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\GraphVariantReader;
use Symfinity\UiKernel\Token\SemanticColourVocabulary;

final class VariantFromGraphTest extends TestCase
{
    #[Test]
    public function graphDerivesColorBackedSemanticVariants(): void
    {
        $graph = (new LayeredTokenResolver())->resolve(
            new LayerStack((new KernelTokenLayers())->baseLayerForTheme(ThemeCatalog::get('default'))),
        );

        $derived = (new GraphVariantReader())->semanticColorVariants($graph);

        foreach (['primary', 'secondary', 'tertiary', 'success', 'danger', 'info', 'warning'] as $variant) {
            self::assertContains($variant, $derived, $variant . ' should be derivable from the graph');
        }
    }

    #[Test]
    public function semanticThemeVocabularyIsGraphAuthoritative(): void
    {
        $vocabulary = SemanticColourVocabulary::fromBuiltInThemeId('semantic');

        self::assertContains('ghost', $vocabulary->all());
        self::assertSame(
            SemanticColourVocabulary::PLATFORM_MINIMUM,
            array_values(array_intersect(SemanticColourVocabulary::PLATFORM_MINIMUM, $vocabulary->all())),
        );
    }
}
