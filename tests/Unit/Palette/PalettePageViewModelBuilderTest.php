<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Palette;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\PalettePageViewModelBuilder;
use Symfinity\UiKernel\Token\MonoTone;
use Symfinity\UiKernel\Token\PaletteCatalog;

final class PalettePageViewModelBuilderTest extends TestCase
{
    #[Test]
    public function itBuildsRampGridsAndSemanticRowsForAdaptivePair(): void
    {
        $model = (new PalettePageViewModelBuilder())->build(
            themeFixed: false,
            fixedThemeId: null,
            adaptiveLightId: 'semantic',
            adaptiveDarkId: 'semantic-dark',
        );

        self::assertCount(count(MonoTone::cases()), $model['ramps']['mono']);
        self::assertCount(count(PaletteCatalog::hueFamilies()), $model['ramps']['hues']);
        self::assertNotEmpty($model['semanticColors']);
        self::assertContains('blue.600', $model['activeRefs']);
        self::assertSame('Semantic', $model['activeThemeLabel']);
        self::assertSame('Semantic dark', $model['activeThemeLabelDark']);
    }
}
