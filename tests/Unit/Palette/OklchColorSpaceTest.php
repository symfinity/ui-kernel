<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Palette;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\OklchColorSpace;
use Symfinity\UiKernel\Palette\OklchTuple;

final class OklchColorSpaceTest extends TestCase
{
    private OklchColorSpace $space;

    protected function setUp(): void
    {
        $this->space = new OklchColorSpace();
    }

    #[Test]
    public function itParsesOklchWithChromaAndHue(): void
    {
        $tuple = $this->space->parse('oklch(0.21 0.006 286)');

        self::assertSame(0.21, $tuple->l);
        self::assertSame(0.006, $tuple->c);
        self::assertSame(286.0, $tuple->h);
        self::assertNull($tuple->alpha);
    }

    #[Test]
    public function itParsesAchromaticOklch(): void
    {
        $tuple = $this->space->parse('oklch(0.995 0 0)');

        self::assertSame(0.995, $tuple->l);
        self::assertSame(0.0, $tuple->c);
        self::assertSame(0.0, $tuple->h);
    }

    #[Test]
    public function pureSpiceAchromaticHasNearZeroChroma(): void
    {
        $tuple = new OklchTuple(0.56, 0.0, 0.0);
        $hex = $this->space->toSrgb($tuple);

        self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $hex);
        self::assertLessThan(0.001, $tuple->c);
        self::assertFalse(is_nan($tuple->l));
    }

    #[Test]
    public function outOfGamutClipReturnsValidHex(): void
    {
        $tuple = new OklchTuple(0.9, 0.5, 120.0);
        $hex = $this->space->toSrgb($tuple);

        self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $hex);
    }

    #[Test]
    public function deltaEIsZeroForIdenticalTuples(): void
    {
        $a = new OklchTuple(0.5, 0.1, 240.0);
        self::assertSame(0.0, $this->space->deltaE($a, $a));
    }

    #[Test]
    public function deltaEUsesCircularHueDifference(): void
    {
        $a = new OklchTuple(0.5, 0.1, 10.0);
        $b = new OklchTuple(0.5, 0.1, 350.0);

        self::assertLessThan(0.2, $this->space->deltaE($a, $b));
    }

    #[Test]
    public function hexRoundTripPreservesApproximateColour(): void
    {
        $original = '#808080';
        $tuple = $this->space->fromHex($original);
        $roundTrip = $this->space->toSrgb($tuple);

        self::assertSame(strtolower($original), strtolower($roundTrip));
    }
}
