<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Token\ThemeConfig;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Token\PaletteRefGrammar;
use Symfinity\UiKernel\Token\ThemeTokenResolver;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class ThemeConfigRefsTest extends TestCase
{
    #[Test]
    public function allThemeRefsPassGrammar(): void
    {
        foreach (ThemeConfig::all() as $config) {
            foreach ($config->colorRefs() as $role => $ref) {
                PaletteRefGrammar::assertValid($ref);
                $this->addToAssertionCount(1);
            }
        }
    }

    #[Test]
    public function themeConfigContainsNoRawHex(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/src/Token/ThemeConfig.php');
        self::assertIsString($source);
        self::assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,8}/', $source);
    }

    #[Test]
    public function lightDarkPairsShareLineagePaletteRecipe(): void
    {
        self::assertSame(
            ThemeConfig::get('default')->paletteRecipe()->hueBase(),
            ThemeConfig::get('default-dark')->paletteRecipe()->hueBase(),
        );
        self::assertSame(
            ThemeConfig::get('semantic')->paletteRecipe()->monoTones(),
            ThemeConfig::get('semantic-dark')->paletteRecipe()->monoTones(),
        );
        self::assertSame(
            ThemeConfig::get('utility')->paletteRecipe()->hueBase(),
            ThemeConfig::get('utility-dark')->paletteRecipe()->hueBase(),
        );
    }

    #[Test]
    public function presetsHaveDistinctPaletteRecipes(): void
    {
        $default = ThemeConfig::get('default')->paletteRecipe();
        $semantic = ThemeConfig::get('semantic')->paletteRecipe();

        self::assertNotSame($default->hueBase()['blue'], $semantic->hueBase()['blue']);
    }

    #[Test]
    public function resolverProducesCompleteSemanticMapForEveryTheme(): void
    {
        $resolver = new ThemeTokenResolver(new \Symfinity\UiKernel\Token\SemanticColorMap(new PaletteGenerator()));

        foreach (ThemeConfig::all() as $config) {
            $tokens = $resolver->resolve($config)->all();
            foreach (ThemeTokenSchema::requiredKeys($config->schemaVersion()) as $key) {
                self::assertArrayHasKey($key, $tokens, $config->id() . ' missing ' . $key);
                self::assertNotSame('', $tokens[$key], $config->id() . ' empty ' . $key);
            }
        }
    }
}
