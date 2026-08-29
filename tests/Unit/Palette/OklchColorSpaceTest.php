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
    public function maxInGamutChromaReturnsAchievableChromaBelowRequestedCeiling(): void
    {
        $max = $this->space->maxInGamutChroma(0.53, 60.0, 0.5);

        self::assertGreaterThan(0.1, $max);
        self::assertLessThan(0.5, $max);
        self::assertTrue($this->space->capToSrgbGamut(new OklchTuple(0.53, $max, 60.0))->c <= $max + 1e-6);
    }

    #[Test]
    public function outOfGamutYellowPreservesHueFamily(): void
    {
        $tuple = new OklchTuple(0.62, 0.428, 55.0);
        $hex = $this->space->toSrgb($tuple);

        self::assertNotSame('#ff0000', strtolower($hex));

        $parts = sscanf(ltrim($hex, '#'), '%2x%2x%2x');
        self::assertIsArray($parts);
        [$r, $g, $b] = $parts;
        self::assertIsInt($r);
        self::assertIsInt($g);
        self::assertIsInt($b);
        self::assertGreaterThan($b, $g, 'yellow family should have green above blue');
        self::assertGreaterThan($b, $r, 'yellow family should have red above blue');
    }

    #[Test]
    public function toCssEmitsOklchLiteral(): void
    {
        $css = $this->space->toCss(new OklchTuple(0.53, 0.12, 55.0));

        self::assertMatchesRegularExpression('/^oklch\([\d.]+\s+[\d.]+\s+[\d.]+\)$/', $css);
    }

    #[Test]
    public function parseColorAcceptsOklchAndHex(): void
    {
        $fromOklch = $this->space->parseColor('oklch(0.53 0.12 55)');
        $fromHex = $this->space->parseColor('#808080');

        self::assertSame(0.53, $fromOklch->l);
        self::assertGreaterThan(0.0, $fromHex->c);
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
