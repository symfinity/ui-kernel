<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Dtcg;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Dtcg\DesignSystemLayerRegistry;
use Symfinity\UiKernel\Dtcg\Exception\UnknownDesignSystemException;
use Symfinity\UiKernel\Dtcg\LayerStackBuilder;
use Symfinity\UiKernel\Theme\ThemeCatalog;

final class DesignSystemLayerRegistryTest extends TestCase
{
    #[Test]
    public function defaultRegistryLoadsSymfinity(): void
    {
        $registry = DesignSystemLayerRegistry::fromDefaultDirectory();

        self::assertTrue($registry->has(DesignSystemLayerRegistry::DEFAULT_ID));
        self::assertSame('design_system:symfinity', $registry->get('symfinity')->id());
    }

    #[Test]
    public function unknownDesignSystemIdThrowsWithIdInMessage(): void
    {
        $registry = DesignSystemLayerRegistry::fromDefaultDirectory();

        try {
            $registry->get('nonexistent-corp-ds');
            self::fail('Expected UnknownDesignSystemException');
        } catch (UnknownDesignSystemException $e) {
            self::assertStringContainsString('nonexistent-corp-ds', $e->getMessage());
        }
    }

    #[Test]
    public function builtinVariantWithMissingDesignSystemFileFailsAtStackBuild(): void
    {
        $variant = ThemeCatalog::variant('default');
        $broken = new \Symfinity\UiKernel\Dtcg\BuiltinThemeVariant(
            'broken-ds-test',
            'Broken DS test',
            $variant->lineage(),
            'missing-design-system-077',
            $variant->layout(),
            $variant->tone(),
            $variant->layerPath(),
            $variant->paletteDefinition(),
        );

        $builder = new LayerStackBuilder(
            new DesignSystemLayerRegistry(DesignSystemLayerRegistry::defaultDirectory()),
        );

        $this->expectException(UnknownDesignSystemException::class);
        $this->expectExceptionMessage('missing-design-system-077');

        $builder->forBuiltinVariant($broken, $variant->paletteRecipe());
    }
}
