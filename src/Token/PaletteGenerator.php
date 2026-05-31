<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use InvalidArgumentException;

/**
 * Internal palette SSOT — mono+spice ramps, hue families, alpha modifier.
 * Uses HSL math (v1); optional anchors preserve showcase parity.
 */
final class PaletteGenerator
{
    /** @var array<int, float> */
    private const LEVEL_LIGHTNESS = [
        50 => 97.0,
        100 => 94.0,
        200 => 88.0,
        300 => 78.0,
        400 => 65.0,
        500 => 52.0,
        600 => 42.0,
        700 => 32.0,
        800 => 22.0,
        900 => 12.0,
        950 => 8.0,
    ];

    /** @var array<string, float> */
    private const HUE_BASE = [
        'red' => 0.0,
        'orange' => 28.0,
        'yellow' => 48.0,
        'green' => 142.0,
        'blue' => 217.0,
        'purple' => 270.0,
    ];

    /** @var array<string, string> */
    private readonly array $anchors;

    /**
     * @param array<string, string>|null $anchors ref => hex/rgb (optional overrides)
     */
    public function __construct(?array $anchors = null)
    {
        $this->anchors = $anchors ?? PaletteAnchors::all();
    }

    public function resolve(string $ref): string
    {
        if (isset($this->anchors[$ref])) {
            return $this->anchors[$ref];
        }

        if (preg_match('/^mono\.([a-z]+)\.(\d+)(?:@(\d+))?$/', $ref, $matches) === 1) {
            $spice = MonoSpice::from($matches[1]);
            $level = (int) $matches[2];
            $alpha = isset($matches[3]) ? (int) $matches[3] : null;
            $hex = $this->monoHex($spice, $level);

            return $alpha !== null ? $this->applyAlpha($hex, $alpha) : $hex;
        }

        if (preg_match('/^([a-z]+)\.(\d+[a-z]?)(?:@(\d+))?$/', $ref, $matches) === 1) {
            $hue = $matches[1];
            $levelToken = $matches[2];
            $alpha = isset($matches[3]) ? (int) $matches[3] : null;

            if (!isset(self::HUE_BASE[$hue])) {
                throw new InvalidArgumentException(sprintf('Unknown hue "%s" in ref "%s".', $hue, $ref));
            }

            $level = (int) preg_replace('/[^0-9]/', '', $levelToken);
            $hex = $this->hueHex($hue, $level);

            return $alpha !== null ? $this->applyAlpha($hex, $alpha) : $hex;
        }

        throw new InvalidArgumentException(sprintf('Invalid palette ref "%s".', $ref));
    }

    public function monoHex(MonoSpice $spice, int $level): string
    {
        $lightness = self::LEVEL_LIGHTNESS[$level] ?? throw new InvalidArgumentException(sprintf('Unknown level %d.', $level));

        return $this->hslToHex($spice->hue(), $spice->saturation(), $lightness);
    }

    public function hueHex(string $hue, int $level): string
    {
        $lightness = self::LEVEL_LIGHTNESS[$level] ?? throw new InvalidArgumentException(sprintf('Unknown level %d.', $level));
        $saturation = $level >= 500 ? 72.0 : 85.0;

        return $this->hslToHex(self::HUE_BASE[$hue], $saturation, $lightness);
    }

    public function applyAlpha(string $hex, int $alphaPercent): string
    {
        $rgb = $this->hexToRgb($hex);
        $alpha = max(0, min(100, $alphaPercent)) / 100;

        return sprintf('rgba(%d, %d, %d, %s)', $rgb[0], $rgb[1], $rgb[2], rtrim(rtrim(sprintf('%.2f', $alpha), '0'), '.'));
    }

    private function hslToHex(float $h, float $s, float $l): string
    {
        $h /= 360;
        $s /= 100;
        $l /= 100;

        if ($s <= 0.0) {
            $v = (int) round($l * 255);

            return sprintf('#%02x%02x%02x', $v, $v, $v);
        }

        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;

        $r = (int) round($this->hueToRgb($p, $q, $h + 1 / 3) * 255);
        $g = (int) round($this->hueToRgb($p, $q, $h) * 255);
        $b = (int) round($this->hueToRgb($p, $q, $h - 1 / 3) * 255);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    private function hueToRgb(float $p, float $q, float $t): float
    {
        if ($t < 0) {
            $t += 1;
        }
        if ($t > 1) {
            $t -= 1;
        }
        if ($t < 1 / 6) {
            return $p + ($q - $p) * 6 * $t;
        }
        if ($t < 1 / 2) {
            return $q;
        }
        if ($t < 2 / 3) {
            return $p + ($q - $p) * (2 / 3 - $t) * 6;
        }

        return $p;
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }
}
