<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Preview;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Preview\InjectedThemeCssProvider;
use Symfinity\UiKernel\Token\AuthoringThemeConfig;
use Symfinity\UiKernel\Token\MonoTone;
use Symfinity\UiKernel\Token\ThemeConfig;
use Symfinity\UiKernel\Token\PresetRegistry;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class InjectedThemeCssParityTest extends TestCase
{
    #[Test]
    public function providerEmitsKernelCssVariablesForSemanticDraft(): void
    {
        $config = $this->sampleConfig();

        $css = (new InjectedThemeCssProvider())->cssFor($config);
        self::assertStringContainsString('--ui-color-primary', $css);
        self::assertStringContainsString('sample-brand', $css);
    }

    #[Test]
    public function providerIsDeterministicForSameConfig(): void
    {
        $config = $this->sampleConfig();
        $provider = new InjectedThemeCssProvider();

        self::assertSame($provider->cssFor($config), $provider->cssFor($config));
    }

    private function sampleConfig(): AuthoringThemeConfig
    {
        $donor = ThemeConfig::get('semantic');
        $appearance = (new PresetRegistry())->tokensFor($donor->layout(), ThemeTokenSchema::V1_0);

        return new AuthoringThemeConfig(
            id: 'sample-brand',
            label: 'Sample Brand',
            layout: $donor->layout(),
            tone: MonoTone::Cool,
            paletteRecipe: $donor->paletteRecipe()->withoutScaleAnchors(),
            colorRefs: [
                'primary' => 'blue.600',
                'secondary' => 'mono.cool.500',
                'tertiary' => 'purple.600',
                'surface' => 'mono.cool.100',
                'surface_elevated' => 'mono.cool.100',
                'text' => 'mono.cool.900',
                'text_muted' => 'mono.cool.400',
                'border' => 'mono.cool.200',
                'danger' => 'red.700',
                'success' => 'green.800',
                'warning' => 'orange.500',
                'info' => 'cyan.600',
                'focus' => 'blue.600',
                'overlay' => 'mono.cool.900@40',
                'skeleton_base' => 'mono.cool.200',
                'skeleton_shine' => 'mono.cool.100',
            ],
            appearanceTokens: $appearance,
            schemaVersion: ThemeTokenSchema::V1_0,
        );
    }
}
