<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Profile;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Profile\SystemProfile;

final class SystemProfileTest extends TestCase
{
    #[Test]
    public function chameleonDefaultHasFiveBreakpointsAndTwelveColumns(): void
    {
        $profile = SystemProfile::chameleonDefault();

        self::assertSame('chameleon-default', $profile->id);
        self::assertSame(12, $profile->columns);
        self::assertSame(
            ['sm' => 640, 'md' => 768, 'lg' => 1024, 'xl' => 1280, '2xl' => 1536],
            $profile->breakpoints,
        );
        self::assertSame(
            ['md' => 720, 'lg' => 960, 'xl' => 1140, '2xl' => 1320],
            $profile->containerMaxWidths,
        );
    }

    #[Test]
    public function fromConfigMergesBreakpointOverrides(): void
    {
        $profile = SystemProfile::fromConfig([
            'breakpoints' => ['md' => 800],
        ]);

        self::assertSame(800, $profile->breakpointPx('md'));
        self::assertSame(1024, $profile->breakpointPx('lg'));
    }

    #[Test]
    public function hashChangesWhenBreakpointsChange(): void
    {
        $a = SystemProfile::chameleonDefault();
        $b = SystemProfile::fromConfig(['breakpoints' => ['md' => 800]]);

        self::assertNotSame($a->hash(), $b->hash());
    }

    #[Test]
    public function zIndexLadderMatchesContract(): void
    {
        $layers = SystemProfile::chameleonDefault()->zIndexLayers();

        self::assertSame(1000, $layers['dropdown']);
        self::assertSame(1080, $layers['toast']);
        self::assertCount(8, $layers);
    }
}
