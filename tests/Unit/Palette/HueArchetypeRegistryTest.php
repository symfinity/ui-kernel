<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Palette;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfinity\UiKernel\Palette\HueArchetypeRegistry;
use Symfinity\UiKernel\Token\PaletteCatalog;

final class HueArchetypeRegistryTest extends TestCase
{
    #[Test]
    public function everyContractHueHasMultiplier(): void
    {
        PaletteCatalog::reset();
        $registry = new HueArchetypeRegistry();

        foreach (PaletteCatalog::hueFamilies() as $hue) {
            $multiplier = $registry->multiplier($hue);
            self::assertGreaterThanOrEqual(0.0, $multiplier);
            self::assertLessThanOrEqual(1.5, $multiplier);
        }
    }

    #[Test]
    public function yellowMultiplierExceedsBlue(): void
    {
        $registry = new HueArchetypeRegistry();

        self::assertGreaterThan($registry->multiplier('blue'), $registry->multiplier('yellow'));
    }

    #[Test]
    public function unknownHueThrows(): void
    {
        $registry = new HueArchetypeRegistry();

        $this->expectException(RuntimeException::class);
        $registry->multiplier('not-a-hue');
    }

    #[Test]
    public function allReturnsFullMap(): void
    {
        $registry = new HueArchetypeRegistry();
        $all = $registry->all();

        self::assertArrayHasKey('red', $all);
        self::assertArrayHasKey('blue', $all);
        self::assertCount(count($all), array_unique(array_keys($all)));
    }
}
