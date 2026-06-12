<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Sparse stallion palette ramps keyed by {@see PaletteRefGrammar} refs.
 *
 * @internal SSOT for theme palette.anchor_profile
 */
final class PaletteAnchorProfiles
{
    /**
     * @return array<string, string> ref => lowercase #hex
     */
    public static function get(string $profile): array
    {
        return match ($profile) {
            'tailwind-v4' => array_merge(self::tailwindV4(), MaterializedPaletteAnchors::TAILWIND_V4_MONO),
            'bootstrap-5.3' => array_merge(self::bootstrap53(), MaterializedPaletteAnchors::BOOTSTRAP_53_MONO),
            'balanced' => MaterializedPaletteAnchors::BALANCED,
            default => throw new \InvalidArgumentException(sprintf('Unknown palette anchor profile "%s".', $profile)),
        };
    }

    /**
     * Tailwind CSS v4 default palette — levels 100–950 (tailwindcolor.com, 2026-06).
     *
     * @return array<string, string>
     */
    private static function tailwindV4(): array
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

        return self::flattenRamps($ramps, $levels);
    }

    /**
     * Bootstrap 5.3 tint/shade ramps from SCSS base colours (twbs/bootstrap v5.3.3).
     *
     * @return array<string, string>
     */
    private static function bootstrap53(): array
    {
        $red = self::bootstrapRamp('#dc3545');
        $orange = self::bootstrapRamp('#fd7e14');
        $yellow = self::bootstrapRamp('#ffc107');
        $green = self::bootstrapRamp('#198754');
        $teal = self::bootstrapRamp('#20c997');
        $cyan = self::bootstrapRamp('#0dcaf0');
        $blue = self::bootstrapRamp('#0d6efd');
        $indigo = self::bootstrapRamp('#6610f2');
        $purple = self::bootstrapRamp('#6f42c1');
        $pink = self::bootstrapRamp('#d63384');
        $lime = self::midpointRamp($yellow, $green);

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

        return self::flattenRamps($ramps, [100, 200, 300, 400, 500, 600, 700, 800, 900, 950]);
    }

    /**
     * @param array<string, list<string>> $ramps
     * @param list<int>                   $levels
     *
     * @return array<string, string>
     */
    private static function flattenRamps(array $ramps, array $levels): array
    {
        $anchors = [];
        foreach ($ramps as $hue => $hexes) {
            foreach ($levels as $index => $level) {
                $anchors[sprintf('%s.%d', $hue, $level)] = $hexes[$index];
            }
        }

        return $anchors;
    }

    /**
     * Bootstrap tint-color / shade-color ramp (100–900) + extrapolated 950.
     *
     * @return list<string>
     */
    private static function bootstrapRamp(string $base): array
    {
        $steps = [];
        foreach ([100 => 80, 200 => 60, 300 => 40, 400 => 20] as $level => $weight) {
            $steps[$level] = self::mixHex($base, '#ffffff', $weight);
        }
        $steps[500] = strtolower($base);
        foreach ([600 => 20, 700 => 40, 800 => 60, 900 => 80] as $level => $weight) {
            $steps[$level] = self::mixHex($base, '#000000', $weight);
        }
        $steps[950] = self::mixHex($steps[900], '#000000', 35);

        return array_values($steps);
    }

    /**
     * @param list<string> $left
     * @param list<string> $right
     *
     * @return list<string>
     */
    private static function midpointRamp(array $left, array $right): array
    {
        $merged = [];
        foreach ($left as $index => $hex) {
            $merged[] = self::midpointHex($hex, $right[$index]);
        }

        return $merged;
    }

    private static function mixHex(string $from, string $with, float $weightPercent): string
    {
        $fromParts = sscanf(ltrim($from, '#'), '%2x%2x%2x');
        $withParts = sscanf(ltrim($with, '#'), '%2x%2x%2x');
        if ($fromParts === null || $withParts === null) {
            throw new \InvalidArgumentException(sprintf('Invalid hex colour "%s" or "%s".', $from, $with));
        }

        $weight = $weightPercent / 100;

        return sprintf(
            '#%02x%02x%02x',
            (int) round($fromParts[0] * (1 - $weight) + $withParts[0] * $weight),
            (int) round($fromParts[1] * (1 - $weight) + $withParts[1] * $weight),
            (int) round($fromParts[2] * (1 - $weight) + $withParts[2] * $weight),
        );
    }

    private static function midpointHex(string $left, string $right): string
    {
        return self::mixHex($left, $right, 50);
    }
}
