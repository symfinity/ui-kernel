<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Theme\LayoutProfile;
use Symfinity\UiKernel\Token\ThemeTokenMap;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class ThemeTokenMapTest extends TestCase
{
    public function testShortKeyMapsToCssVariable(): void
    {
        self::assertSame('--ui-space-md', ThemeTokenMap::shortKeyToCssVar('space-md'));
        self::assertSame('space-md', ThemeTokenMap::cssVarToShortKey('--ui-space-md'));
    }

    public function testRejectsPrefixedShortKeys(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ThemeTokenMap::shortKeyToCssVar('--ui-space-md');
    }

    public function testPresetShortTokensMatchLayoutKeys(): void
    {
        $short = ThemeTokenMap::presetShortTokensFor(LayoutProfile::Semantic);
        ThemeTokenMap::assertComplete($short, ThemeTokenSchema::V1_0, 'test');

        $css = ThemeTokenMap::toCssVariables($short);
        foreach (ThemeTokenSchema::LAYOUT_KEYS as $layoutKey) {
            self::assertArrayHasKey($layoutKey, $css);
        }
    }
}
