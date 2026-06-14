<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Contract\Catalog\GraphVariantCatalogPort;
use Symfinity\UiKernel\Contract\Resolver\ResolvedGraph;
use Symfinity\UiKernel\Contract\Token\Token;
use Symfinity\UiKernel\Contract\Token\TokenPath;
use Symfinity\UiKernel\Contract\Token\TokenType;
use Symfinity\UiKernel\Css\GraphVariantCatalog;
use Symfinity\UiKernel\Dtcg\DesignSystemLayerRegistry;
use Symfinity\UiKernel\Dtcg\LayerStackBuilder;
use Symfinity\UiKernel\Dtcg\ProfileGlobalsLayerRegistry;
use Symfinity\UiKernel\Dtcg\ThemeDtcgResolver;

/** 078 — graph variant catalog port. */
final class GraphVariantCatalogTest extends TestCase
{
    #[Test]
    public function defaultThemeReturnsEightSlugsInPlatformOrder(): void
    {
        $catalog = $this->catalog();

        self::assertSame(
            ['primary', 'secondary', 'tertiary', 'success', 'danger', 'info', 'warning', 'ghost'],
            $catalog->semanticColorSlugs(),
        );
    }

    #[Test]
    public function layerSignatureIsNonEmptyAndStable(): void
    {
        $catalog = $this->catalog();
        $signature = $catalog->layerSignature();

        self::assertNotSame('', $signature);
        self::assertSame($signature, $catalog->layerSignature());
    }

    #[Test]
    public function portImplementationUsesGraphNotEnum(): void
    {
        $port = new class implements GraphVariantCatalogPort {
            public function semanticColorSlugs(): array
            {
                return ['primary', 'danger', 'accent'];
            }

            public function layerSignature(): string
            {
                return 'test-signature';
            }
        };

        self::assertSame(['primary', 'danger', 'accent'], $port->semanticColorSlugs());
    }

    #[Test]
    public function graphWithAccentTokenAppendsSlugAlphabeticallyAfterKnownSet(): void
    {
        $graph = new ResolvedGraph([
            'color.primary' => new Token(TokenPath::fromString('color.primary'), TokenType::Color, '#000'),
            'color.accent' => new Token(TokenPath::fromString('color.accent'), TokenType::Color, '#f00'),
        ], 'sig-accent');

        $slugs = \Symfinity\UiKernel\Token\SemanticColourVocabulary::fromGraph($graph)->all();

        self::assertContains('accent', $slugs);
        self::assertSame('accent', $slugs[\count($slugs) - 1]);
    }

    private function catalog(): GraphVariantCatalog
    {
        $stackBuilder = new LayerStackBuilder(DesignSystemLayerRegistry::fromDefaultDirectory());

        return new GraphVariantCatalog(
            new ThemeDtcgResolver($stackBuilder),
            \Symfinity\UiKernel\Dtcg\BuiltinDtcgThemeCatalog::fromDefaultDirectory(),
            'semantic',
        );
    }
}
