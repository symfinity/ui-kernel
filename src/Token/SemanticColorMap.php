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
    public function resolve(array $roleRefs, ThemePaletteRecipe $recipe, ?MonoTone $themeTone = null): array
    {
        $colors = [];
        foreach ($roleRefs as $role => $ref) {
            if (!isset(self::ROLE_TO_CSS[$role])) {
                throw new \InvalidArgumentException(sprintf('Unknown semantic colour role "%s".', $role));
            }
            $resolvedRef = $themeTone !== null ? self::applyThemeTone($ref, $themeTone) : $ref;
            $colors[self::ROLE_TO_CSS[$role]] = $this->palette->resolveToCss($resolvedRef, $recipe);
        }

        return $colors;
    }

    /**
     * Rewrites tinted mono refs to the theme's active tone.
     *
     * `mono.pure.*` stays achromatic — built-ins use it for explicit neutral surfaces.
     */
    public static function applyThemeTone(string $ref, MonoTone $themeTone): string
    {
        if (preg_match('/^mono\.([a-z]+)\.(\d+)(@\d+)?$/', $ref, $matches) !== 1) {
            return $ref;
        }

        if ($matches[1] === MonoTone::Pure->value) {
            return $ref;
        }

        return sprintf('mono.%s.%s%s', $themeTone->value, $matches[2], $matches[3] ?? '');
    }
}
