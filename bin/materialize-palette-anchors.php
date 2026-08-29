<?php

declare(strict_types=1);

/**
 * One-shot generator for MaterializedPaletteAnchors — no PaletteAnchorProfiles dependency.
 *
 * @internal maintainer tool; run from product monorepo root:
 *   ./bin/php packages/ui-kernel/bin/materialize-palette-anchors.php
 */

use Symfony\Component\Yaml\Yaml;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

require dirname(__DIR__, 3) . '/vendor/autoload.php';

function mixHex(string $from, string $with, float $weightPercent): string
{
    $fromParts = sscanf(ltrim($from, '#'), '%2x%2x%2x');
    $withParts = sscanf(ltrim($with, '#'), '%2x%2x%2x');
    if ($fromParts === null || $withParts === null) {
        throw new InvalidArgumentException('Invalid hex');
    }
    $weight = $weightPercent / 100;

    return sprintf(
        '#%02x%02x%02x',
        (int) round($fromParts[0] * (1 - $weight) + $withParts[0] * $weight),
        (int) round($fromParts[1] * (1 - $weight) + $withParts[1] * $weight),
        (int) round($fromParts[2] * (1 - $weight) + $withParts[2] * $weight),
    );
}

/**
 * @param array<string, list<string>> $ramps
 * @param list<int>                   $levels
 *
 * @return array<string, string>
 */
function flattenRamps(array $ramps, array $levels): array
{
    $anchors = [];
    foreach ($ramps as $hue => $hexes) {
        foreach ($levels as $index => $level) {
            $anchors[sprintf('%s.%d', $hue, $level)] = strtolower($hexes[$index]);
        }
    }

    return $anchors;
}

function bootstrapRamp(string $base): array
{
    $steps = [];
    foreach ([100 => 80, 200 => 60, 300 => 40, 400 => 20] as $level => $weight) {
        $steps[$level] = mixHex($base, '#ffffff', $weight);
    }
    $steps[500] = strtolower($base);
    foreach ([600 => 20, 700 => 40, 800 => 60, 900 => 80] as $level => $weight) {
        $steps[$level] = mixHex($base, '#000000', $weight);
    }
    $steps[950] = mixHex($steps[900], '#000000', 35);

    return array_values($steps);
}

/**
 * @param list<string> $left
 * @param list<string> $right
 *
 * @return list<string>
 */
function midpointRamp(array $left, array $right): array
{
    $merged = [];
    foreach ($left as $index => $hex) {
        $merged[] = mixHex($hex, $right[$index], 50);
    }

    return $merged;
}

function tailwindV4Anchors(): array
{
    $levels = [100, 200, 300, 400, 500, 600, 700, 800, 900, 950];
    $ramps = [
        'red' => ['#ffe2e2', '#ffc9c9', '#ffa2a2', '#ff6467', '#fb2c36', '#e7000b', '#c10007', '#9f0712', '#82181a', '#460809'],
        'orange' => ['#ffedd4', '#ffd6a7', '#ffb86a', '#ff8904', '#ff6900', '#f54900', '#ca3500', '#9f2d00', '#7e2a0c', '#441306'],
        'yellow' => ['#fef9c2', '#fff085', '#ffdf20', '#fdc700', '#f0b100', '#d08700', '#a65f00', '#894b00', '#733e0a', '#432004'],
        'lime' => ['#ecfcca', '#d8f999', '#bbf451', '#9ae600', '#7ccf00', '#5ea500', '#497d00', '#3c6300', '#35530e', '#192e03'],
        'green' => ['#dcfce7', '#b9f8cf', '#7bf1a8', '#05df72', '#00c950', '#00a63e', '#008236', '#016630', '#0d542b', '#032e15'],
        'emerald' => ['#d0fae5', '#a4f4cf', '#5ee9b5', '#00d492', '#00bc7d', '#009966', '#007a55', '#006045', '#004f3b', '#002c22'],
        'teal' => ['#cbfbf1', '#96f7e4', '#46ecd5', '#00d3bd', '#00b9a6', '#009488', '#00776e', '#005f5a', '#064c49', '#022f2e'],
        'cyan' => ['#cefafe', '#a2f4fd', '#53eafd', '#00d3f2', '#00b8db', '#0092b8', '#007595', '#005f78', '#104e64', '#053345'],
        'sky' => ['#dff2fe', '#b8e6fe', '#74d4ff', '#00bcff', '#00a6f4', '#0084d1', '#0069a8', '#00598a', '#024a70', '#052f4a'],
        'blue' => ['#dbeafe', '#bedbff', '#8ec5ff', '#51a2ff', '#2b7fff', '#155dfc', '#1447e6', '#193cb8', '#1c398e', '#162456'],
        'violet' => ['#ede9fe', '#ddd6ff', '#c4b4ff', '#a684ff', '#8e51ff', '#7f22fe', '#7008e7', '#5d0ec0', '#4d179a', '#2f0d68'],
        'purple' => ['#f3e8ff', '#e9d4ff', '#dab2ff', '#c27aff', '#ad46ff', '#9810fa', '#8200db', '#6e11b0', '#59168b', '#3c0366'],
        'pink' => ['#fce7f3', '#fccee8', '#fda5d5', '#fb64b6', '#f6339a', '#e60076', '#c6005c', '#a3004c', '#861043', '#510424'],
    ];

    return flattenRamps($ramps, $levels);
}

function bootstrap53Anchors(): array
{
    $red = bootstrapRamp('#dc3545');
    $orange = bootstrapRamp('#fd7e14');
    $yellow = bootstrapRamp('#ffc107');
    $green = bootstrapRamp('#198754');
    $teal = bootstrapRamp('#20c997');
    $cyan = bootstrapRamp('#0dcaf0');
    $blue = bootstrapRamp('#0d6efd');
    $indigo = bootstrapRamp('#6610f2');
    $purple = bootstrapRamp('#6f42c1');
    $pink = bootstrapRamp('#d63384');
    $lime = midpointRamp($yellow, $green);

    $ramps = [
        'red' => $red,
        'orange' => $orange,
        'yellow' => $yellow,
        'lime' => $lime,
        'green' => $green,
        'emerald' => $green,
        'teal' => $teal,
        'cyan' => $cyan,
        'sky' => $cyan,
        'blue' => $blue,
        'violet' => $indigo,
        'purple' => $purple,
        'pink' => $pink,
    ];

    return flattenRamps($ramps, [100, 200, 300, 400, 500, 600, 700, 800, 900, 950]);
}

function balancedHueAnchors(array $tw, array $bs): array
{
    $neutral = '#64748b';
    $pureHues = ['red', 'orange', 'yellow', 'green', 'emerald', 'teal', 'cyan', 'sky', 'blue'];
    $warmHues = ['yellow', 'lime', 'pink'];
    $balanced = [];

    foreach (PaletteCatalog::hueFamilies() as $hue) {
        foreach (PaletteCatalog::levels() as $level) {
            $ref = $hue . '.' . $level;
            $mid = mixHex($tw[$ref], $bs[$ref], 50);

            if (!in_array($hue, $pureHues, true)) {
                if ($level >= 300 && $level <= 700) {
                    $desat = in_array($hue, $warmHues, true) ? 10.0 : 7.0;
                    $mid = mixHex($mid, $neutral, $desat);
                }
                if ($level === 500 || $level === 600) {
                    $mid = mixHex($mid, $neutral, 3.0);
                }
            }

            $balanced[$ref] = strtolower($mid);
        }
    }

    return $balanced;
}

/**
 * @return array{hue_base: array<string, float>, mono_tones: array<string, array{hue: float, saturation: float}>}
 */
function paletteRecipeInputs(string $themePath, string $lineageKey): array
{
    /** @var array<string, mixed> $parsed */
    $parsed = Yaml::parseFile($themePath);
    /** @var array<string, mixed> $palette */
    $palette = $parsed['symfinity_ui_kernel']['themes'][$lineageKey]['palette'];

    $hueBase = [];
    foreach ($palette['hues'] as $name => $value) {
        $hueBase[(string) $name] = (float) $value;
    }

    $monoTones = [];
    foreach ($palette['mono'] as $name => $tone) {
        $monoTones[(string) $name] = [
            'hue' => (float) ($tone['hue'] ?? 0),
            'saturation' => (float) ($tone['saturation'] ?? 0),
        ];
    }

    return ['hue_base' => $hueBase, 'mono_tones' => $monoTones];
}

/**
 * @param array{hue_base: array<string, float>, mono_tones: array<string, array{hue: float, saturation: float}>} $inputs
 *
 * @return array<string, string>
 */
function materializeMono(array $inputs): array
{
    $recipe = ThemePaletteRecipe::fromPaletteDefinition($inputs['hue_base'], $inputs['mono_tones']);
    $generator = new PaletteGenerator([]);
    $mono = [];

    foreach (PaletteCatalog::monoTones() as $tone) {
        foreach (PaletteCatalog::levels() as $level) {
            $ref = 'mono.' . $tone . '.' . $level;
            $mono[$ref] = strtolower($generator->resolve($ref, $recipe));
        }
    }

    return $mono;
}

function exportConst(string $name, array $anchors): string
{
    $lines = ["    public const {$name} = ["];
    ksort($anchors);
    foreach ($anchors as $k => $v) {
        $lines[] = "        '{$k}' => '{$v}',";
    }
    $lines[] = '    ];';

    return implode("\n", $lines);
}

$themeDir = dirname(__DIR__) . '/config/themes';
$tw = tailwindV4Anchors();
$bs = bootstrap53Anchors();
$balanced = array_merge(
    balancedHueAnchors($tw, $bs),
    materializeMono(paletteRecipeInputs($themeDir . '/default.yaml', 'default')),
);
$bootstrapMono = materializeMono(paletteRecipeInputs($themeDir . '/semantic.yaml', 'semantic'));
$tailwindMono = materializeMono(paletteRecipeInputs($themeDir . '/utility.yaml', 'utility'));

$outPath = dirname(__DIR__) . '/src/Token/MaterializedPaletteAnchors.php';
$content = <<<'PHP'
<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Frozen materialized palette anchors (hue + mono) — palette-freeze v1.
 *
 * Balanced hue ramps: TW/BS midpoint; state hues (red–blue) without slate desat.
 *
 * @internal SSOT; regenerate via packages/ui-kernel/bin/materialize-palette-anchors.php
 */
final class MaterializedPaletteAnchors
{
PHP;

$content .= "\n" . exportConst('BALANCED', $balanced) . "\n\n";
$content .= exportConst('BOOTSTRAP_53_MONO', $bootstrapMono) . "\n\n";
$content .= exportConst('TAILWIND_V4_MONO', $tailwindMono) . "\n";
$content .= "}\n";

file_put_contents($outPath, $content);
fwrite(STDERR, sprintf("Wrote %s (%d balanced refs)\n", $outPath, count($balanced)));
