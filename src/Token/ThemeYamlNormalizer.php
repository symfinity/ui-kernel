<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use Symfinity\UiKernel\Theme\LayoutProfile;

/**
 * Normalizes schema 1.0 theme YAML (grouped tokens, nested colours) to the internal flat model.
 */
final class ThemeYamlNormalizer
{
    /** @var array<string, string> */
    private const COLOR_ROLE_MAP = [
        'brand.primary' => 'primary',
        'brand.secondary' => 'secondary',
        'brand.tertiary' => 'tertiary',
        'surface.base' => 'surface',
        'surface.elevated' => 'surface_elevated',
        'surface.overlay' => 'overlay',
        'text.default' => 'text',
        'text.muted' => 'text_muted',
        'border.default' => 'border',
        'state.danger' => 'danger',
        'state.success' => 'success',
        'state.warning' => 'warning',
        'state.info' => 'info',
        'state.focus' => 'focus',
        'skeleton.base' => 'skeleton_base',
        'skeleton.shine' => 'skeleton_shine',
    ];

    /** @var array<string, LayoutProfile> */
    private const LINEAGE_LAYOUT = [
        'default' => LayoutProfile::Semantic,
        'semantic' => LayoutProfile::Semantic,
        'utility' => LayoutProfile::Utility,
    ];

    /**
     * @param array<string, mixed> $palette
     *
     * @return array{
     *     hue_base: array<string, float>,
     *     mono_tones: array<string, array{hue: float, saturation: float}>,
     *     hue_chroma: array<string, float>,
     *     scale_anchors: array<string, string>
     * }
     */
    public static function normalizePalette(array $palette): array
    {
        $hues = $palette['hues'] ?? null;
        $mono = $palette['mono'] ?? null;
        $chroma = $palette['chroma'] ?? [];
        $anchors = $palette['anchors'] ?? [];
        $anchorProfile = $palette['anchor_profile'] ?? null;

        if (!is_array($hues) || !is_array($mono) || $hues === [] || $mono === []) {
            throw new \InvalidArgumentException('Theme palette must define hues and mono.');
        }

        $hueBase = [];
        foreach ($hues as $name => $value) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException(sprintf('Palette hue "%s" must be numeric.', (string) $name));
            }
            $hueBase[(string) $name] = (float) $value;
        }

        $monoTones = [];
        foreach ($mono as $name => $tone) {
            if (!is_array($tone)) {
                throw new \InvalidArgumentException(sprintf('Mono tone "%s" must be a mapping with hue and saturation.', (string) $name));
            }
            $monoTones[(string) $name] = [
                'hue' => (float) ($tone['hue'] ?? 0),
                'saturation' => (float) ($tone['saturation'] ?? 0),
            ];
        }

        $hueChroma = [];
        if ($chroma !== []) {
            if (!is_array($chroma)) {
                throw new \InvalidArgumentException('Theme palette.chroma must be a mapping when present.');
            }
            foreach ($chroma as $name => $value) {
                if (!is_numeric($value)) {
                    throw new \InvalidArgumentException(sprintf('Palette chroma "%s" must be numeric.', (string) $name));
                }
                $hueChroma[(string) $name] = (float) $value;
            }
            $unknown = array_diff(array_keys($hueChroma), PaletteCatalog::hueFamilies());
            if ($unknown !== []) {
                throw new \InvalidArgumentException(sprintf(
                    'Palette chroma has unknown hue families: %s.',
                    implode(', ', $unknown),
                ));
            }
        }

        $scaleAnchors = [];
        if (is_string($anchorProfile) && $anchorProfile !== '') {
            $scaleAnchors = PaletteAnchorProfiles::get($anchorProfile);
        }
        if ($anchors !== []) {
            if (!is_array($anchors)) {
                throw new \InvalidArgumentException('Theme palette.anchors must be a mapping when present.');
            }
            foreach ($anchors as $ref => $hex) {
                if (!is_string($ref) || !is_string($hex)) {
                    throw new \InvalidArgumentException('Theme palette.anchors entries must be ref => hex strings.');
                }
                PaletteRefGrammar::assertValid($ref);
                if (!preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $hex)) {
                    throw new \InvalidArgumentException(sprintf('Theme palette anchor "%s" must be a #hex colour.', $ref));
                }
                $scaleAnchors[$ref] = strtolower($hex);
            }
        }

        return [
            'hue_base' => $hueBase,
            'mono_tones' => $monoTones,
            'hue_chroma' => $hueChroma,
            'scale_anchors' => $scaleAnchors,
        ];
    }

    /**
     * @param array<string, mixed> $tokens grouped token tree (schema 1.0)
     *
     * @return array<string, string>
     */
    public static function flattenTokens(array $tokens): array
    {
        if ($tokens === []) {
            return [];
        }

        $flat = [];
        self::walkTokenTree($tokens, '', $flat);

        return $flat;
    }

    /**
     * @param array<string, mixed> $colors nested colour groups (schema 1.0)
     *
     * @return array<string, string>
     */
    public static function flattenColors(array $colors): array
    {
        if ($colors === []) {
            return [];
        }

        $flat = [];
        foreach ($colors as $group => $roles) {
            if (!is_string($group) || !is_array($roles)) {
                throw new \InvalidArgumentException('Theme colours must be grouped (brand, surface, text, …).');
            }
            foreach ($roles as $role => $ref) {
                if (!is_string($role) || !is_string($ref)) {
                    throw new \InvalidArgumentException(sprintf('Theme colour "%s.%s" must be a palette ref string.', $group, (string) $role));
                }
                $path = $group . '.' . $role;
                $flatKey = self::COLOR_ROLE_MAP[$path] ?? null;
                if ($flatKey === null) {
                    throw new \InvalidArgumentException(sprintf('Unknown nested colour role "%s".', $path));
                }
                $flat[$flatKey] = $ref;
            }
        }

        return $flat;
    }

    /**
     * @param array<string, mixed> $variant
     *
     * @return array<string, mixed>
     */
    public static function mergeVariantDefinition(array $variant, array $parent): array
    {
        $merged = $parent;
        foreach ($variant as $key => $value) {
            if ($key === 'colors' && is_array($value) && isset($merged['colors']) && is_array($merged['colors'])) {
                $merged['colors'] = self::mergeColorTrees($merged['colors'], $value);
                continue;
            }
            if ($key === 'tokens' && is_array($value) && isset($merged['tokens']) && is_array($merged['tokens'])) {
                $merged['tokens'] = array_replace_recursive($merged['tokens'], $value);
                continue;
            }
            $merged[$key] = $value;
        }

        return $merged;
    }

    public static function variantKeyToId(string $variantKey): string
    {
        return str_replace('_', '-', $variantKey);
    }

    public static function layoutForLineage(string $lineageKey): string
    {
        if (!isset(self::LINEAGE_LAYOUT[$lineageKey])) {
            throw new \InvalidArgumentException(sprintf('Unknown theme lineage "%s".', $lineageKey));
        }

        return self::LINEAGE_LAYOUT[$lineageKey]->name;
    }

    /**
     * @param array<string, mixed> $group
     *
     * @return list<array<string, mixed>>
     */
    public static function expandVariants(array $group, string $lineageKey, string $path): array
    {
        $palette = $group['palette'] ?? null;
        if (!is_array($palette)) {
            throw new \InvalidArgumentException(sprintf('Theme file "%s" symfinity_ui_kernel.themes.%s must define palette.', $path, $lineageKey));
        }

        $paletteNorm = self::normalizePalette($palette);
        $lineageTokens = is_array($group['tokens'] ?? null) ? $group['tokens'] : [];

        $variants = $group['variants'] ?? null;
        if (!is_array($variants) || $variants === []) {
            throw new \InvalidArgumentException(sprintf('Theme file "%s" symfinity_ui_kernel.themes.%s.variants must be non-empty.', $path, $lineageKey));
        }

        /** @var array<string, array<string, mixed>> $resolved */
        $resolved = [];
        foreach ($variants as $variantKey => $variant) {
            if (!is_string($variantKey) || !is_array($variant)) {
                throw new \InvalidArgumentException(sprintf('Theme file "%s": invalid variant "%s".', $path, (string) $variantKey));
            }

            $extends = $variant['extends'] ?? null;
            if (is_string($extends)) {
                if (!isset($resolved[$extends])) {
                    throw new \InvalidArgumentException(sprintf('Theme file "%s": variant "%s" extends unknown "%s".', $path, $variantKey, $extends));
                }
                $variant = self::mergeVariantDefinition($variant, $resolved[$extends]);
            }

            $resolved[$variantKey] = $variant;
        }

        $expanded = [];
        foreach ($resolved as $variantKey => $variant) {
            $variantTokens = is_array($variant['tokens'] ?? null) ? $variant['tokens'] : [];
            $tokens = self::flattenTokens(array_replace_recursive($lineageTokens, $variantTokens));

            $colors = self::flattenColors(is_array($variant['colors'] ?? null) ? $variant['colors'] : []);

            $label = $variant['label'] ?? null;
            if (!is_string($label) || $label === '') {
                throw new \InvalidArgumentException(sprintf('Theme file "%s": variant "%s" missing label.', $path, $variantKey));
            }

            $expanded[] = [
                'id' => self::variantKeyToId($variantKey),
                'label' => $label,
                'layout' => self::layoutForLineage($lineageKey),
                'tone' => (string) ($variant['tone'] ?? 'cool'),
                'colors' => $colors,
                'hue_base' => $paletteNorm['hue_base'],
                'mono_tones' => $paletteNorm['mono_tones'],
                'hue_chroma' => $paletteNorm['hue_chroma'],
                'scale_anchors' => $paletteNorm['scale_anchors'],
                'tokens' => $tokens,
                'lineage' => $lineageKey,
                'scroll_motion' => (bool) ($variant['scroll_motion'] ?? false),
                'backdrop_blur' => is_string($variant['backdrop_blur'] ?? null) ? $variant['backdrop_blur'] : '0',
            ];
        }

        return $expanded;
    }

    /**
     * @param array<string, mixed> $parent
     * @param array<string, mixed> $child
     *
     * @return array<string, mixed>
     */
    private static function mergeColorTrees(array $parent, array $child): array
    {
        $merged = $parent;
        foreach ($child as $group => $roles) {
            if (!is_string($group)) {
                continue;
            }
            if (!isset($merged[$group]) || !is_array($merged[$group])) {
                $merged[$group] = [];
            }
            if (is_array($roles)) {
                /** @var array<string, mixed> $mergedGroup */
                $mergedGroup = $merged[$group];
                foreach ($roles as $role => $ref) {
                    $mergedGroup[(string) $role] = $ref;
                }
                $merged[$group] = $mergedGroup;
            }
        }

        return $merged;
    }

    /**
     * @param array<string, mixed> $node
     * @param array<string, string> $out
     */
    private static function walkTokenTree(array $node, string $prefix, array &$out): void
    {
        foreach ($node as $key => $value) {
            if (!is_string($key)) {
                continue;
            }
            if (is_string($value)) {
                $out[self::tokenPathToShortKey($prefix, $key)] = $value;
                continue;
            }
            if (is_array($value)) {
                self::walkTokenTree($value, self::joinTokenPrefix($prefix, $key), $out);
            }
        }
    }

    private static function joinTokenPrefix(string $prefix, string $segment): string
    {
        if ($prefix === '') {
            return $segment;
        }

        return $prefix . '.' . $segment;
    }

    private static function tokenPathToShortKey(string $prefix, string $leaf): string
    {
        $path = $prefix === '' ? $leaf : $prefix . '.' . $leaf;

        return match ($path) {
            'grid_gap', 'grid-gap' => 'grid-gap',
            'space.2xl' => 'space-2xl',
            'space.grid_gap' => 'grid-gap',
            default => str_replace(
                ['font.family.', 'font.size.', 'font.weight.', 'font.line_height.', 'motion.duration.', 'motion.easing.', 'focus.ring.', 'space.', 'radius.', 'shadow.'],
                ['font-family-', 'font-size-', 'font-weight-', 'line-height-', 'motion-duration-', 'motion-easing-', 'focus-ring-', 'space-', 'radius-', 'shadow-'],
                $path,
            ),
        };
    }
}
