<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Theme\LayoutProfile;
use Symfinity\UiKernel\Theme\PhysicsRegistry;
use Symfinity\UiKernel\Token\PresetRegistry;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class PresetRegistryTest extends TestCase
{
    #[Test]
    public function presetsUseDistinctRhythms(): void
    {
        $registry = new PresetRegistry();

        $semantic = $registry->tokensFor(LayoutProfile::Semantic, ThemeTokenSchema::V1_0);
        $utility = $registry->tokensFor(LayoutProfile::Utility, ThemeTokenSchema::V1_0);

        self::assertNotSame($semantic['--ui-space-xl'], $utility['--ui-space-xl']);
        self::assertNotSame($semantic['--ui-font-size-md'], $utility['--ui-font-size-md']);
    }

    #[Test]
    public function presetExportsZeroPhysicsOwnedKeys(): void
    {
        $registry = new PresetRegistry();
        $tokens = $registry->tokensFor(LayoutProfile::Semantic, ThemeTokenSchema::V1_0);

        foreach (PhysicsRegistry::PRESET_FORBIDDEN_KEYS as $key) {
            self::assertArrayNotHasKey($key, $tokens, 'Preset must not export ' . $key);
        }
    }

    #[Test]
    public function presetIncludesFocusRingTokens(): void
    {
        $registry = new PresetRegistry();
        $tokens = $registry->tokensFor(LayoutProfile::Semantic, ThemeTokenSchema::V1_0);

        self::assertArrayHasKey('--ui-focus-ring-width', $tokens);
        self::assertArrayHasKey('--ui-focus-ring-opacity', $tokens);
        self::assertArrayHasKey('--ui-focus-ring-blur', $tokens);
    }
}
