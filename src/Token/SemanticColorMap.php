<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use Symfinity\UiKernel\Palette\PaletteGenerator;

/**
 * Maps semantic colour roles to CSS custom property keys and resolves palette refs.
 */
final class SemanticColorMap
{
    /** @var array<string, string> role => --ui-color-* */
    public const ROLE_TO_CSS = [
        'primary' => '--ui-color-primary',
        'secondary' => '--ui-color-secondary',
        'tertiary' => '--ui-color-tertiary',
        'surface' => '--ui-color-surface',
        'surface_elevated' => '--ui-color-surface-elevated',
        'text' => '--ui-color-text',
        'text_muted' => '--ui-color-text-muted',
        'border' => '--ui-color-border',
        'danger' => '--ui-color-danger',
        'success' => '--ui-color-success',
        'warning' => '--ui-color-warning',
        'info' => '--ui-color-info',
        'focus' => '--ui-color-focus',
        'overlay' => '--ui-color-overlay',
        'skeleton_base' => '--ui-color-skeleton-base',
        'skeleton_shine' => '--ui-color-skeleton-shine',
    ];

    public function __construct(
        private readonly PaletteGenerator $palette = new PaletteGenerator(),
    ) {
    }

    /**
     * @param array<string, string> $roleRefs semantic role => palette ref
     *
     * @return array<string, string> --ui-color-* => value
     */
    /**
     * @param array<string, string> $roleRefs
     */
    public function resolve(array $roleRefs, ThemePaletteRecipe $recipe): array
    {
        $colors = [];
        foreach ($roleRefs as $role => $ref) {
            if (!isset(self::ROLE_TO_CSS[$role])) {
                throw new \InvalidArgumentException(sprintf('Unknown semantic colour role "%s".', $role));
            }
            $colors[self::ROLE_TO_CSS[$role]] = $this->palette->resolve($ref, $recipe);
        }

        return $colors;
    }
}
