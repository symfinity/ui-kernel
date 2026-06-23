<?php

declare(strict_types=1);

/**
 * One-shot exporter: legacy symfinity_ui_kernel.themes YAML → 077 DTCG on-disk layout.
 *
 * Usage (from symfinity repo root):
 *   ./sbin/php packages/ui-kernel/tests/bin/export-dtcg-themes.php
 */

require dirname(__DIR__, 4) . '/vendor/autoload.php';

use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Token\BuiltinThemeCatalog;
use Symfinity\UiKernel\Token\MonoTone;
use Symfinity\UiKernel\Token\SemanticColorMap;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;
use Symfinity\UiKernel\Token\ThemePaletteMetaNormalizer;
use Symfony\Component\Yaml\Yaml;

$packageRoot = dirname(__DIR__, 2);
$themesRoot = $packageRoot . '/config/themes';
$designSystemsRoot = $packageRoot . '/config/design-systems';

/** @var array<string, string> flat role => DTCG path */
const COLOR_ROLE_TO_DTCG = [
    'primary' => 'color.primary',
    'secondary' => 'color.secondary',
    'tertiary' => 'color.tertiary',
    'surface' => 'color.surface.base',
    'surface_elevated' => 'color.surface.elevated',
    'text' => 'color.text.default',
    'text_muted' => 'color.text.muted',
    'border' => 'color.border.default',
    'danger' => 'color.danger',
    'success' => 'color.success',
    'warning' => 'color.warning',
    'info' => 'color.info',
    'focus' => 'color.focus',
    'overlay' => 'color.overlay',
    'skeleton_base' => 'color.skeleton.base',
    'skeleton_shine' => 'color.skeleton.shine',
];

/** @var array<string, list<string>> lineage => list of variant ids in order */
$lineageVariants = [];

foreach (BuiltinThemeCatalog::themes() as $theme) {
    $lineage = $theme['lineage'];
    $lineageVariants[$lineage][] = $theme['id'];
}

foreach ($lineageVariants as $lineage => $variantIds) {
    $lineageDir = $themesRoot . '/' . $lineage;
    if (!is_dir($lineageDir)) {
        mkdir($lineageDir, 0755, true);
    }

    $legacyPath = $themesRoot . '/' . $lineage . '.yaml';
    if (!is_file($legacyPath)) {
        fwrite(STDERR, "Skip {$lineage}: no legacy file at {$legacyPath}\n");
        continue;
    }

    /** @var array<string, mixed> $legacy */
    $legacy = Yaml::parseFile($legacyPath);
    $group = $legacy['symfinity_ui_kernel']['themes'][$lineage] ?? null;
    if (!is_array($group)) {
        throw new RuntimeException("Invalid legacy theme group for {$lineage}");
    }

    $palette = ThemePaletteMetaNormalizer::normalize($group['palette']);
    $metaVariants = [];

    foreach (BuiltinThemeCatalog::themes() as $theme) {
        if ($theme['lineage'] !== $lineage) {
            continue;
        }

        $recipe = ThemePaletteRecipe::fromPaletteDefinition(
            $theme['hue_base'],
            $theme['mono_tones'],
            $theme['hue_chroma'] ?? [],
            $theme['scale_anchors'] ?? [],
        );
        $generator = new PaletteGenerator();
        $tone = MonoTone::from($theme['tone']);

        $dtcgTree = [];

        foreach ($theme['colors'] as $role => $ref) {
            $path = COLOR_ROLE_TO_DTCG[$role] ?? null;
            if ($path === null) {
                throw new RuntimeException("Unknown color role {$role} in {$theme['id']}");
            }

            $resolvedRef = SemanticColorMap::applyThemeTone($ref, $tone);
            setNestedDtcgValue($dtcgTree, $path, colorValueNode($resolvedRef, $recipe, $generator));
        }

        foreach ($theme['tokens'] as $shortKey => $value) {
            $path = shortKeyToDtcgPath($shortKey);
            setNestedDtcgValue($dtcgTree, $path, appearanceValueNode($path, $value));
        }

        $layerFile = str_replace('/', '-', $theme['id']) . '.dtcg.yaml';
        $layerPath = $lineageDir . '/' . $layerFile;
        file_put_contents($layerPath, Yaml::dump($dtcgTree, 6, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));

        $metaVariants[] = [
            'id' => $theme['id'],
            'layer_file' => $layerFile,
            'label' => $theme['label'],
            'tone' => $theme['tone'],
            'mode' => str_contains($theme['id'], 'dark') ? 'dark' : 'light',
        ];

        if ($theme['scroll_motion'] ?? false) {
            $metaVariants[array_key_last($metaVariants)]['scroll_motion'] = true;
        }
        if (($theme['backdrop_blur'] ?? '0') !== '0') {
            $metaVariants[array_key_last($metaVariants)]['backdrop_blur'] = $theme['backdrop_blur'];
        }
    }

    $meta = [
        'lineage' => $lineage,
        'design_system_id' => 'symfinity',
        'palette' => $group['palette'],
        'variants' => $metaVariants,
    ];

    file_put_contents($lineageDir . '/theme.meta.yaml', Yaml::dump($meta, 4, 2));
    echo "Exported {$lineage}: " . count($metaVariants) . " variants\n";
}

if (!is_dir($designSystemsRoot)) {
    mkdir($designSystemsRoot, 0755, true);
}

$symfinityDesignSystem = [
    'color' => [
        'ghost' => [
            '$type' => 'color',
            '$value' => '{color.text.muted}',
            '$description' => 'Ghost variant maps to muted text (060 vocabulary parity)',
        ],
    ],
    '$extensions' => [
        'symfinity' => [
            'design_system_id' => 'symfinity',
            'semantic_colors' => ['primary', 'secondary', 'tertiary', 'success', 'danger', 'info', 'warning', 'ghost'],
        ],
    ],
];

file_put_contents($designSystemsRoot . '/symfinity.dtcg.yaml', Yaml::dump($symfinityDesignSystem, 4, 2));
echo "Wrote symfinity.dtcg.yaml\n";

/**
 * @param array<string, mixed> $tree
 */
function setNestedDtcgValue(array &$tree, string $path, array $node): void
{
    $segments = explode('.', $path);
    $cursor = &$tree;
    foreach ($segments as $i => $segment) {
        if ($i === \count($segments) - 1) {
            $cursor[$segment] = $node;
            return;
        }
        if (!isset($cursor[$segment]) || !is_array($cursor[$segment]) || array_key_exists('$value', $cursor[$segment])) {
            $cursor[$segment] = [];
        }
        $cursor = &$cursor[$segment];
    }
}

function colorValueNode(string $ref, ThemePaletteRecipe $recipe, PaletteGenerator $generator): array
{
    if (str_contains($ref, '@')) {
        return [
            '$type' => 'color',
            '$value' => $generator->resolveToCss($ref, $recipe),
        ];
    }

    return [
        '$type' => 'color',
        '$value' => paletteRefToAlias($ref),
    ];
}

function appearanceValueNode(string $path, string $value): array
{
    $type = match (true) {
        str_starts_with($path, 'motion.duration.') => 'duration',
        str_starts_with($path, 'motion.easing.') => 'cubicBezier',
        str_starts_with($path, 'font.weight.') => 'number',
        str_starts_with($path, 'focus.ring.') && !str_contains($value, 'rem') && !str_contains($value, 'px') => 'number',
        str_starts_with($path, 'space.') || str_starts_with($path, 'radius.') || str_starts_with($path, 'focus.ring.') => 'dimension',
        str_starts_with($path, 'font.size.') => 'dimension',
        str_starts_with($path, 'font.line_height.') => 'number',
        default => 'unknown',
    };

    return ['$type' => $type, '$value' => $value];
}

function paletteRefToAlias(string $ref): string
{
    if (preg_match('/^mono\.([a-z]+)\.(\d+)$/', $ref, $matches) === 1) {
        return sprintf('{color.mono.%s.%s}', $matches[1], $matches[2]);
    }

    if (preg_match('/^([a-z]+)\.(\d+)$/', $ref, $matches) === 1) {
        return sprintf('{color.%s.%s}', $matches[1], $matches[2]);
    }

    throw new RuntimeException('Invalid palette ref: ' . $ref);
}

function shortKeyToDtcgPath(string $shortKey): string
{
    if (preg_match('/^line-height-(.+)$/', $shortKey, $matches) === 1) {
        return 'line-height.' . $matches[1];
    }

    return match ($shortKey) {
        'grid-gap' => 'grid.gap',
        'space-2xl' => 'space.2xl',
        default => str_replace(
            ['font-family-', 'font-size-', 'font-weight-', 'motion-duration-', 'motion-easing-', 'focus-ring-', 'space-', 'radius-', 'shadow-'],
            ['font.family.', 'font.size.', 'font.weight.', 'motion.duration.', 'motion.easing.', 'focus.ring.', 'space.', 'radius.', 'shadow.'],
            $shortKey,
        ),
    };
}
