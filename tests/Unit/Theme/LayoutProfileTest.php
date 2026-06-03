<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Theme;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Theme\LayoutProfile;

final class LayoutProfileTest extends TestCase
{
    #[Test]
    public function presetsUseDistinctLayoutRhythms(): void
    {
        $kiroshi = LayoutProfile::Kiroshi->layout();
        $semantic = LayoutProfile::Semantic->layout();
        $utility = LayoutProfile::Utility->layout();

        self::assertSame('0', $kiroshi['--ui-radius-md']);
        self::assertSame('0.375rem', $semantic['--ui-radius-md']);
        self::assertSame('0.25rem', $utility['--ui-radius-md']);

        self::assertSame('0.625rem', $kiroshi['--ui-space-md']);
        self::assertSame('1rem', $semantic['--ui-space-md']);
        self::assertSame('0.75rem', $utility['--ui-space-md']);

        self::assertNotSame($semantic['--ui-space-xl'], $utility['--ui-space-xl']);
        self::assertNotSame($semantic['--ui-font-size-md'], $utility['--ui-font-size-md']);
    }
}
