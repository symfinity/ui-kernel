<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Palette;

use Symfinity\UiKernel\Token\ThemePaletteRecipe;
use Symfinity\UiKernel\Token\ThemeConfig;
use Symfinity\UiKernel\Token\MonoTone;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\PaletteRefGrammar;
use Symfinity\UiKernel\Token\SemanticColorMap;

/**
 * View model for palette documentation pages (ramps + semantic role resolution).
 */
final class PalettePageViewModelBuilder
{
    public function __construct(
        private readonly PaletteGenerator $palette = new PaletteGenerator(),
    ) {
    }

    /**
     * @return array{
     *     semanticColors: list<array{role: string, cssVar: string, paletteRef: string, paletteRefDark: ?string, resolved: string, resolvedDark: ?string}>,
     *     ramps: array{
     *         mono: list<array{tone: string, label: string, steps: array<int, string>}>,
     *         hues: list<array{family: string, steps: array<int, string>}>
     *     },
     *     rampRecipeLabel: string,
     *     activeRefs: list<string>,
     *     activeThemeLabel: string,
     *     activeThemeLabelDark: ?string
     * }
     */
    public function build(
        bool $themeFixed,
        ?string $fixedThemeId,
        string $adaptiveLightId,
        string $adaptiveDarkId,
    ): array {
        if ($themeFixed && $fixedThemeId !== null) {
            $config = ThemeConfig::get($fixedThemeId);
            $activeRefs = array_values(array_unique(array_values($config->colorRefs())));

            return [
                'semanticColors' => $this->semanticRows($config->colorRefs(), null, $config->paletteRecipe(), null),
                'ramps' => $this->rampGrids($config->paletteRecipe()),
                'rampRecipeLabel' => $config->label(),
                'activeRefs' => $activeRefs,
                'activeThemeLabel' => $config->label(),
                'activeThemeLabelDark' => null,
            ];
        }

        $lightConfig = ThemeConfig::get($adaptiveLightId);
        $darkConfig = ThemeConfig::get($adaptiveDarkId);
        $activeRefs = array_values(array_unique([
            ...array_values($lightConfig->colorRefs()),
            ...array_values($darkConfig->colorRefs()),
        ]));

        return [
            'semanticColors' => $this->semanticRows(
                $lightConfig->colorRefs(),
                $darkConfig->colorRefs(),
                $lightConfig->paletteRecipe(),
                $darkConfig->paletteRecipe(),
            ),
            'ramps' => $this->rampGrids($lightConfig->paletteRecipe()),
            'rampRecipeLabel' => $lightConfig->label(),
            'activeRefs' => $activeRefs,
            'activeThemeLabel' => $lightConfig->label(),
            'activeThemeLabelDark' => $darkConfig->label(),
        ];
    }

    /**
     * @return array{
     *     mono: list<array{spice: string, label: string, steps: array<int, string>}>,
     *     hues: list<array{family: string, steps: array<int, string>}>
     * }
     */
    private function rampGrids(ThemePaletteRecipe $recipe): array
    {
        $mono = [];
        foreach (MonoTone::cases() as $tone) {
            $mono[] = [
                'tone' => $tone->value,
                'label' => ucfirst($tone->value),
                'steps' => $this->palette->rampPreview('mono', $recipe, $tone),
            ];
        }

        $hues = [];
        foreach (PaletteCatalog::hueFamilies() as $family) {
            $hues[] = [
                'family' => $family,
                'steps' => $this->palette->rampPreview($family, $recipe),
            ];
        }

        return ['mono' => $mono, 'hues' => $hues];
    }

    /**
     * @param array<string, string>      $lightRefs
     * @param array<string, string>|null $darkRefs
     *
     * @return list<array{role: string, cssVar: string, paletteRef: string, paletteRefDark: ?string, resolved: string, resolvedDark: ?string}>
     */
    private function semanticRows(
        array $lightRefs,
        ?array $darkRefs,
        ThemePaletteRecipe $lightRecipe,
        ?ThemePaletteRecipe $darkRecipe,
    ): array {
        $rows = [];
        foreach (SemanticColorMap::ROLE_TO_CSS as $role => $cssVar) {
            if (!isset($lightRefs[$role])) {
                continue;
            }

            $lightRef = $lightRefs[$role];
            PaletteRefGrammar::assertValid($lightRef);

            $darkRef = $darkRefs[$role] ?? null;
            if ($darkRef !== null) {
                PaletteRefGrammar::assertValid($darkRef);
            }

            $rows[] = [
                'role' => $role,
                'cssVar' => $cssVar,
                'paletteRef' => $lightRef,
                'paletteRefDark' => $darkRef,
                'resolved' => $this->palette->resolve($lightRef, $lightRecipe),
                'resolvedDark' => $darkRefs !== null && $darkRecipe !== null
                    ? $this->palette->resolve($darkRef ?? $lightRef, $darkRecipe)
                    : null,
            ];
        }

        return $rows;
    }
}
