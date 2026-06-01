<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Profile;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Profile\SystemProfile;
use Symfinity\UiKernel\Profile\SystemProfileRegistry;

final class SystemProfileRegistryTest extends TestCase
{
    #[Test]
    public function resolveReturnsChameleonDefaultWhenConfigEmpty(): void
    {
        $registry = new SystemProfileRegistry();

        $profile = $registry->resolve();

        self::assertSame(SystemProfile::DEFAULT_ID, $profile->id);
        self::assertSame(12, $profile->columns);
    }

    #[Test]
    public function resolveIsCached(): void
    {
        $registry = new SystemProfileRegistry(['breakpoints' => ['md' => 800]]);

        self::assertSame($registry->resolve(), $registry->resolve());
        self::assertSame(800, $registry->resolve()->breakpointPx('md'));
    }

    #[Test]
    public function configOverrideDoesNotAffectUserTokenSet(): void
    {
        $registry = new SystemProfileRegistry(['breakpoints' => ['md' => 900]]);

        self::assertSame(900, $registry->resolve()->breakpointPx('md'));
        self::assertSame(768, SystemProfile::chameleonDefault()->breakpointPx('md'));
    }
}
