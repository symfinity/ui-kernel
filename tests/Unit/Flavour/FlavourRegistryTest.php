<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Flavour;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Flavour\FlavourCatalog;
use Symfinity\UiKernel\Flavour\FlavourRegistry;
use Symfinity\UiKernel\Token\DesignTokenSet;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class FlavourRegistryTest extends TestCase
{
    #[Test]
    public function itRejectsIncompleteTokenMaps(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        DesignTokenSet::fromArray(['--ui-color-primary' => '#000']);
    }

    #[Test]
    public function itRegistersSixFlavoursIncludingForeignDarkVariants(): void
    {
        $registry = new FlavourRegistry();
        self::assertCount(6, $registry->all());
        self::assertSame('Kiroshi', $registry->get('default')->label());
        self::assertSame('semantic-dark', $registry->get('semantic-dark')->id());
        self::assertSame('utility-dark', $registry->get('utility-dark')->id());
    }

    #[Test]
    public function unknownThemeFallsBackToDefault(): void
    {
        $registry = new FlavourRegistry();
        self::assertSame('default', $registry->resolve('not-a-theme')->id());
    }

    #[Test]
    public function defaultFlavourHasAllSchemaKeys(): void
    {
        $tokens = FlavourCatalog::get('default')->tokens()->all();
        foreach (ThemeTokenSchema::REQUIRED_KEYS as $key) {
            self::assertArrayHasKey($key, $tokens);
        }
    }

    #[Test]
    public function catalogRejectsIncompleteColorMaps(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new \Symfinity\UiKernel\Flavour\DefinedFlavour(
            'broken',
            'Broken',
            \Symfinity\UiKernel\Flavour\LayoutProfile::Kiroshi,
            ['--ui-color-primary' => '#000'],
        );
    }
}
