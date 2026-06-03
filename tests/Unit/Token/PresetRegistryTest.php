<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Theme\LayoutProfile;
use Symfinity\UiKernel\Token\PresetRegistry;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class PresetRegistryTest extends TestCase
{
    #[Test]
    public function presetsUseDistinctRhythms(): void
    {
        $registry = new PresetRegistry();

        $kiroshi = $registry->tokensFor(LayoutProfile::Kiroshi, ThemeTokenSchema::V2_0);
        $semantic = $registry->tokensFor(LayoutProfile::Semantic, ThemeTokenSchema::V2_0);
        $utility = $registry->tokensFor(LayoutProfile::Utility, ThemeTokenSchema::V2_0);

        self::assertSame('0', $kiroshi['--ui-radius-md']);
        self::assertSame('0.375rem', $semantic['--ui-radius-md']);
        self::assertSame('0.25rem', $utility['--ui-radius-md']);

        self::assertNotSame($semantic['--ui-space-xl'], $utility['--ui-space-xl']);
        self::assertNotSame($semantic['--ui-font-size-md'], $utility['--ui-font-size-md']);
    }

    #[Test]
    public function schemaTwoIncludesMotionAndFocusRingTokens(): void
    {
        $registry = new PresetRegistry();
        $tokens = $registry->tokensFor(LayoutProfile::Semantic, ThemeTokenSchema::V2_0);

        self::assertArrayHasKey('--ui-motion-duration-normal', $tokens);
        self::assertArrayHasKey('--ui-shadow-md', $tokens);
        self::assertArrayHasKey('--ui-focus-ring-width', $tokens);
        self::assertArrayNotHasKey('--ui-transition-duration', $tokens);
    }
}
