<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Theme;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Theme\PhysicsId;
use Symfinity\UiKernel\Theme\PhysicsRegistry;

final class PhysicsRegistryTest extends TestCase
{
    #[Test]
    public function eachProfileDefinesRequiredTokenKeys(): void
    {
        $registry = new PhysicsRegistry();

        foreach (PhysicsId::cases() as $id) {
            $tokens = $registry->tokensFor($id);
            foreach (PhysicsRegistry::PHYSICS_TOKEN_KEYS as $key) {
                self::assertArrayHasKey($key, $tokens, $id->value . ' missing ' . $key);
                self::assertNotSame('', $tokens[$key]);
            }
        }
    }

    #[Test]
    public function bridgeAliasesReferencePhysicsTokens(): void
    {
        $registry = new PhysicsRegistry();

        foreach (PhysicsId::cases() as $id) {
            $bridges = $registry->bridgeAliases($id);
            self::assertStringContainsString('--ui-physics-', $bridges['--ui-radius-md']);
            self::assertStringContainsString('--ui-physics-', $bridges['--ui-motion-duration-normal']);
        }
    }

    #[Test]
    public function retroProfileUsesZeroRadiusAndInstantMotion(): void
    {
        $tokens = (new PhysicsRegistry())->tokensFor(PhysicsId::Retro);

        self::assertSame('0', $tokens['--ui-physics-radius-md']);
        self::assertSame('0ms', $tokens['--ui-physics-motion-duration-normal']);
        self::assertStringContainsString('steps(', $tokens['--ui-physics-motion-easing-standard']);
    }
}
