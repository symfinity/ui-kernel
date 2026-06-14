<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Dtcg\BuiltinThemeVariant;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Tests\Support\ThemeDtcgResolverFactory;
use Symfinity\UiKernel\Token\MonoTone;
use Symfinity\UiKernel\Token\SemanticColorMap;
use Symfinity\UiKernel\Token\ThemeConfig;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

final class SemanticColorMapThemeToneTest extends TestCase
{
    #[Test]
    public function applyThemeToneRewritesTintedMonoRefs(): void
    {
        self::assertSame('mono.warm.900', SemanticColorMap::applyThemeTone('mono.cool.900', MonoTone::Warm));
        self::assertSame('mono.warm.900@40', SemanticColorMap::applyThemeTone('mono.cool.900@40', MonoTone::Warm));
    }

    #[Test]
    public function applyThemeTonePreservesPureMono(): void
    {
        self::assertSame('mono.pure.100', SemanticColorMap::applyThemeTone('mono.pure.100', MonoTone::Warm));
    }

    #[Test]
    public function applyThemeToneLeavesHueRefsUntouched(): void
    {
        self::assertSame('blue.600', SemanticColorMap::applyThemeTone('blue.600', MonoTone::Warm));
    }

    #[Test]
    public function themeToneChangesResolvedMonoRoles(): void
    {
        $recipe = ThemePaletteRecipe::baseline();
        $generator = new PaletteGenerator();
        $map = new SemanticColorMap($generator);

        $refs = [
            'text' => 'mono.cool.900',
            'surface' => 'mono.pure.100',
            'primary' => 'blue.600',
        ];

        $cool = $map->resolve($refs, $recipe, MonoTone::Cool);
        $warm = $map->resolve($refs, $recipe, MonoTone::Warm);

        self::assertNotSame($cool['--ui-color-text'], $warm['--ui-color-text']);
        self::assertSame($cool['--ui-color-surface'], $warm['--ui-color-surface']);
        self::assertSame($cool['--ui-color-primary'], $warm['--ui-color-primary']);
    }

    #[Test]
    public function resolverUsesThemeConfigTone(): void
    {
        $semantic = ThemeCatalog::variant('semantic');
        $resolver = ThemeDtcgResolverFactory::create();

        $cool = $resolver->resolve(self::variantWithTone($semantic, MonoTone::Cool))->all();
        $warm = $resolver->resolve(self::variantWithTone($semantic, MonoTone::Warm))->all();

        self::assertNotSame($cool['--ui-color-text'], $warm['--ui-color-text']);
        self::assertSame($cool['--ui-color-primary'], $warm['--ui-color-primary']);
    }

    private static function variantWithTone(BuiltinThemeVariant $base, MonoTone $tone): BuiltinThemeVariant
    {
        return new BuiltinThemeVariant(
            $base->id() . '-' . $tone->value,
            $base->label(),
            $base->lineage(),
            $base->designSystemId(),
            $base->layout(),
            $tone,
            $base->layerPath(),
            $base->paletteDefinition(),
            $base->schemaVersion(),
            $base->scrollMotion(),
            $base->backdropBlur(),
        );
    }
}
