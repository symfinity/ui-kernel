<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Token\ColorMode;
use Symfinity\UiKernel\Token\CompoundShadowBuilder;
use Symfinity\UiKernel\Token\LineageId;
use Symfinity\UiKernel\Token\ShadowTier;

final class CompoundShadowBuilderTest extends TestCase
{
    private CompoundShadowBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new CompoundShadowBuilder();
    }

    #[Test]
    public function composeProducesTwoLayerShadowWithoutBlogLiterals(): void
    {
        $shadow = $this->builder->compose(
            ShadowTier::Md,
            LineageId::Semantic,
            ColorMode::Light,
            'oklch(0.96 0.01 260)',
        );

        self::assertStringContainsString('inset 0 1px 0', $shadow);
        self::assertStringContainsString(',', $shadow);
        self::assertStringContainsString('color-mix(in oklch', $shadow);
        self::assertStringNotContainsString('rgba(255,255,255', $shadow);
        self::assertStringNotContainsString('rgba(0, 0, 0', $shadow);
    }

    #[Test]
    public function tierOrderingIsPerceptuallySmallerToLarger(): void
    {
        $surface = 'oklch(0.95 0.01 260)';

        foreach ([LineageId::Kiroshi, LineageId::Semantic, LineageId::Utility] as $lineage) {
            $sm = $this->builder->compose(ShadowTier::Sm, $lineage, ColorMode::Light, $surface);
            $md = $this->builder->compose(ShadowTier::Md, $lineage, ColorMode::Light, $surface);
            $lg = $this->builder->compose(ShadowTier::Lg, $lineage, ColorMode::Light, $surface);

            self::assertSame('2px', $this->offsetY($sm), $lineage->value);
            self::assertSame('4px', $this->offsetY($md), $lineage->value);
            self::assertSame('8px', $this->offsetY($lg), $lineage->value);
        }
    }

    #[Test]
    public function darkModeUsesStrongerDropAlphaThanLight(): void
    {
        $surface = 'oklch(0.25 0.01 260)';

        $light = $this->builder->compose(ShadowTier::Md, LineageId::Kiroshi, ColorMode::Light, $surface);
        $dark = $this->builder->compose(ShadowTier::Md, LineageId::Kiroshi, ColorMode::Dark, $surface);

        self::assertNotSame($light, $dark);
        self::assertGreaterThan(
            $this->dropAlphaPercent($light),
            $this->dropAlphaPercent($dark),
        );
    }

    #[Test]
    public function applyToTokenMapUsesResolvedSurfaceElevated(): void
    {
        $tokens = $this->builder->applyToTokenMap(
            ['--ui-color-surface-elevated' => 'oklch(0.91 0.02 240)'],
            LineageId::Utility,
            ColorMode::Light,
        );

        self::assertArrayHasKey('--ui-shadow-sm', $tokens);
        self::assertArrayHasKey('--ui-shadow-md', $tokens);
        self::assertArrayHasKey('--ui-shadow-lg', $tokens);
        self::assertStringContainsString('oklch(0.91 0.02 240)', $tokens['--ui-shadow-md']);
    }

    #[Test]
    public function lineageOrderingCapsUtilityBelowSemantic(): void
    {
        $surface = 'oklch(0.95 0.01 260)';

        $utility = $this->dropAlphaPercent($this->builder->compose(
            ShadowTier::Md,
            LineageId::Utility,
            ColorMode::Light,
            $surface,
        ));
        $semantic = $this->dropAlphaPercent($this->builder->compose(
            ShadowTier::Md,
            LineageId::Semantic,
            ColorMode::Light,
            $surface,
        ));

        self::assertLessThan($semantic, $utility);
    }

    private function offsetY(string $shadow): string
    {
        if (preg_match('/,\s*0\s+(\d+px)\s+/', $shadow, $matches) !== 1) {
            self::fail('Could not parse drop offset from shadow: ' . $shadow);
        }

        return $matches[1];
    }

    private function dropAlphaPercent(string $shadow): float
    {
        if (preg_match('/black\s+([\d.]+)%/', $shadow, $matches) !== 1) {
            self::fail('Could not parse drop alpha from shadow: ' . $shadow);
        }

        return (float) $matches[1];
    }
}
