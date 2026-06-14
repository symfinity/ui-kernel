<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Token\BuiltinThemeCatalog;
use Symfinity\UiKernel\Token\MaterializedPaletteAnchors;
use Symfinity\UiKernel\Token\PaletteAnchorProfiles;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\SemanticColorDerivatives;
use Symfinity\UiKernel\Token\SemanticColorMap;
use Symfinity\UiKernel\Token\ThemeConfig;
use Symfinity\UiKernel\Token\ThemeTokenResolver;

/** Guards palette-freeze revision 1 — frozen anchor hex values must not drift. */
final class PaletteFreezeTest extends TestCase
{
    private const FROZEN_REVISION = 1;

    /** @var array<string, string> */
    private const BALANCED_STATE_HEX = [
        'blue.500' => '#1c77fe',
        'blue.600' => '#105be3',
        'red.500' => '#ec313e',
        'green.500' => '#0da852',
        'orange.500' => '#fe740a',
    ];

    protected function setUp(): void
    {
        BuiltinThemeCatalog::reset();
        PaletteCatalog::reset();
    }

    protected function tearDown(): void
    {
        BuiltinThemeCatalog::reset();
        PaletteCatalog::reset();
    }

    #[Test]
    public function generatorRevisionIsFrozen(): void
    {
        self::assertSame(self::FROZEN_REVISION, PaletteCatalog::revision());
    }

    #[Test]
    public function materializedBalancedProfileHasOneHundredNinetyRefs(): void
    {
        self::assertCount(190, MaterializedPaletteAnchors::BALANCED);
        self::assertCount(60, MaterializedPaletteAnchors::BOOTSTRAP_53_MONO);
        self::assertCount(60, MaterializedPaletteAnchors::TAILWIND_V4_MONO);
    }

    #[Test]
    public function balancedStateHueAnchorsMatchFreezeTable(): void
    {
        $balanced = MaterializedPaletteAnchors::BALANCED;

        foreach (self::BALANCED_STATE_HEX as $ref => $hex) {
            self::assertArrayHasKey($ref, $balanced, $ref);
            self::assertSame($hex, $balanced[$ref], $ref);
        }
    }

    #[Test]
    public function defaultThemeSemanticRolesResolveToFrozenHex(): void
    {
        $generator = new PaletteGenerator();
        $config = ThemeConfig::get('default');
        $resolved = (new SemanticColorMap($generator))->resolve(
            $config->colorRefs(),
            $config->paletteRecipe(),
        );

        self::assertSame('#105be3', $resolved['--ui-color-primary']);
        self::assertSame('#ec313e', $resolved['--ui-color-danger']);
        self::assertSame('#0da852', $resolved['--ui-color-success']);
        self::assertSame('#fe740a', $resolved['--ui-color-warning']);
    }

    #[Test]
    public function frozenHexSemanticTokensAreNotP3Boosted(): void
    {
        $tokens = (new ThemeTokenResolver())->resolve(ThemeConfig::get('default'))->all();
        $keys = array_column((new SemanticColorDerivatives())->p3Boosts($tokens), 'key');

        foreach (['--ui-color-primary', '--ui-color-danger', '--ui-color-success', '--ui-color-warning'] as $key) {
            self::assertNotContains($key, $keys, $key);
        }
    }

    #[Test]
    public function shippedLineagesUseFrozenAnchorProfiles(): void
    {
        foreach (['default' => 'balanced', 'semantic' => 'bootstrap-5.3', 'utility' => 'tailwind-v4'] as $themeId => $profile) {
            $anchors = ThemeConfig::get($themeId)->paletteRecipe()->scaleAnchors();
            self::assertCount(190, $anchors, $themeId);
            self::assertSame(
                PaletteAnchorProfiles::get($profile),
                $anchors,
                $themeId . ' anchor profile',
            );
        }
    }
}
