<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Token\MonoSpice;
use Symfinity\UiKernel\Token\PaletteGenerator;

final class PaletteGeneratorTest extends TestCase
{
    #[Test]
    public function itResolvesAnchoredShowcaseRefs(): void
    {
        $generator = new PaletteGenerator();

        self::assertSame('#0a0a0a', $generator->resolve('mono.warm.950'));
        self::assertSame('#0d6efd', $generator->resolve('blue.600'));
    }

    #[Test]
    public function monoSpiceLevelsDiffer(): void
    {
        $generator = new PaletteGenerator();

        $light = $generator->monoHex(MonoSpice::Cool, 50);
        $dark = $generator->monoHex(MonoSpice::Cool, 950);

        self::assertNotSame($light, $dark);
    }

    #[Test]
    public function spicesProduceDistinctMonoRamps(): void
    {
        $generator = new PaletteGenerator();

        self::assertNotSame(
            $generator->monoHex(MonoSpice::Pure, 500),
            $generator->monoHex(MonoSpice::Warm, 500),
        );
    }

    #[Test]
    public function itAppliesAlphaModifier(): void
    {
        $generator = new PaletteGenerator();

        self::assertSame('rgba(15, 23, 42, 0.4)', $generator->resolve('mono.cool.950@40'));
    }

    #[Test]
    public function generatedHueOutputIsHex(): void
    {
        $generator = new PaletteGenerator();
        $hex = $generator->hueHex('blue', 600);

        self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $hex);
        self::assertStringNotContainsString('oklch', $hex);
    }
}
