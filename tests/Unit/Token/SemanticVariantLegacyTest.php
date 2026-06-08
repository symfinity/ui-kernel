<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Token\Appearance;
use Symfinity\UiKernel\Token\InteractiveSurfaceProps;
use Symfinity\UiKernel\Token\SemanticVariant;

final class SemanticVariantLegacyTest extends TestCase
{
    #[Test]
    public function coerceLegacyMapsDeprecatedAliases(): void
    {
        self::assertSame('primary', SemanticVariant::coerceLegacy('default'));
        self::assertSame('primary', SemanticVariant::coerceLegacy(''));
        self::assertSame('danger', SemanticVariant::coerceLegacy('destructive'));
        self::assertSame('secondary', SemanticVariant::coerceLegacy('secondary'));
    }

    #[Test]
    public function normalizeColourPropsCoercesInvalidValuesToPrimary(): void
    {
        $normalized = SemanticVariant::normalizeColourProps(['variant' => 'default'], 'variant');

        self::assertSame('primary', $normalized['variant']);
    }

    #[Test]
    public function interactiveSurfacePropsSplitsLegacyOutlineAndLinkVariants(): void
    {
        self::assertSame(
            ['variant' => 'primary', 'appearance' => Appearance::OUTLINE],
            InteractiveSurfaceProps::normalize(['variant' => 'outline']),
        );
        self::assertSame(
            ['variant' => 'primary', 'appearance' => Appearance::LINK],
            InteractiveSurfaceProps::normalize(['variant' => 'link']),
        );
        self::assertSame(
            ['variant' => 'danger', 'appearance' => Appearance::SOLID],
            InteractiveSurfaceProps::normalize(['variant' => 'destructive', 'appearance' => 'solid']),
        );
    }
}
