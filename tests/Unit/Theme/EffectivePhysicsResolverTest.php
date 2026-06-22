<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Theme;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Theme\EffectivePhysicsResolver;
use Symfinity\UiKernel\Theme\PhysicsId;

final class EffectivePhysicsResolverTest extends TestCase
{
    private EffectivePhysicsResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new EffectivePhysicsResolver();
    }

    #[Test]
    #[DataProvider('matrixCases')]
    public function resolveMatrix(
        PhysicsId $requested,
        bool $variantIsDark,
        PhysicsId $expectedEffective,
        bool $expectedCorrected,
    ): void {
        $resolution = $this->resolver->resolve($requested, $variantIsDark);

        self::assertSame($expectedEffective, $resolution->effective);
        self::assertSame($requested, $resolution->requested);
        self::assertSame($expectedCorrected, $resolution->corrected);
        if ($expectedCorrected) {
            self::assertNotNull($resolution->correctionReason);
        }
    }

    /**
     * @return iterable<string, array{PhysicsId, bool, PhysicsId, bool}>
     */
    public static function matrixCases(): iterable
    {
        yield 'glass + dark' => [PhysicsId::Glass, true, PhysicsId::Glass, false];
        yield 'glass + light' => [PhysicsId::Glass, false, PhysicsId::Flat, true];
        yield 'flat + light' => [PhysicsId::Flat, false, PhysicsId::Flat, false];
        yield 'flat + dark' => [PhysicsId::Flat, true, PhysicsId::Flat, false];
        yield 'retro + light' => [PhysicsId::Retro, false, PhysicsId::Retro, false];
        yield 'retro + dark' => [PhysicsId::Retro, true, PhysicsId::Retro, false];
    }

    #[Test]
    public function invalidStringCoercesToFlat(): void
    {
        $resolution = $this->resolver->resolveFromStrings('neon', false);

        self::assertSame(PhysicsId::Flat, $resolution->effective);
        self::assertSame(PhysicsId::Flat, $resolution->requested);
    }

    #[Test]
    public function variantIsDarkDetectsDarkSuffix(): void
    {
        self::assertTrue($this->resolver->variantIsDark('default-dark'));
        self::assertTrue($this->resolver->variantIsDark('semantic-dark'));
        self::assertFalse($this->resolver->variantIsDark('default'));
        self::assertFalse($this->resolver->variantIsDark('utility'));
    }
}
