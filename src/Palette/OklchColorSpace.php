<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Palette;

use InvalidArgumentException;

/**
 * OKLCH colour space utilities — parse, ΔE, gamut clip → sRGB hex.
 *
 * ΔE uses Euclidean distance in OKLCH with circular hue difference (degrees).
 * This is a stable, documented approximation for nearest-ref sampling (055).
 */
final class OklchColorSpace
{
    public function parse(string $css): OklchTuple
    {
        $css = trim(strtolower($css));

        if (preg_match(
            '/^oklch\(\s*([+-]?(?:\d+\.?\d*|\.\d+)(?:e[+-]?\d+)?)\s+([+-]?(?:\d+\.?\d*|\.\d+)(?:e[+-]?\d+)?)\s+([+-]?(?:\d+\.?\d*|\.\d+)(?:e[+-]?\d+)?)(?:\s*\/\s*([+-]?(?:\d+\.?\d*|\.\d+)(?:e[+-]?\d+)?))?\s*\)$/',
            $css,
            $matches,
        ) !== 1) {
            throw new InvalidArgumentException(sprintf('Cannot parse OKLCH colour "%s".', $css));
        }

        $alpha = isset($matches[4]) && $matches[4] !== '' ? (float) $matches[4] : null;

        return new OklchTuple((float) $matches[1], (float) $matches[2], (float) $matches[3], $alpha);
    }

    public function toSrgb(OklchTuple $tuple): string
    {
        [$r, $g, $b] = $this->oklchToLinearSrgb($tuple);

        return sprintf(
            '#%02x%02x%02x',
            (int) round($this->linearToSrgb($r) * 255),
            (int) round($this->linearToSrgb($g) * 255),
            (int) round($this->linearToSrgb($b) * 255),
        );
    }

    public function fromHex(string $hex): OklchTuple
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            throw new InvalidArgumentException(sprintf('Cannot parse hex colour "#%s".', $hex));
        }

        return $this->fromLinearSrgb([
            $this->srgbToLinear(hexdec(substr($hex, 0, 2)) / 255),
            $this->srgbToLinear(hexdec(substr($hex, 2, 2)) / 255),
            $this->srgbToLinear(hexdec(substr($hex, 4, 2)) / 255),
        ]);
    }

    public function deltaE(OklchTuple $a, OklchTuple $b): float
    {
        $dh = abs($a->h - $b->h);
        $dh = min($dh, 360.0 - $dh) / 180.0;

        $dl = $a->l - $b->l;
        $dc = $a->c - $b->c;

        return sqrt($dl * $dl + $dc * $dc + $dh * $dh);
    }

    /**
     * @param array{0: float, 1: float, 2: float} $linear
     */
    public function fromLinearSrgb(array $linear): OklchTuple
    {
        $l = $linear[0];
        $m = $linear[1];
        $s = $linear[2];

        $l_ = pow($l, 1.0 / 3.0);
        $m_ = pow($m, 1.0 / 3.0);
        $s_ = pow($s, 1.0 / 3.0);

        $L = 0.2104542553 * $l_ + 0.7936177850 * $m_ - 0.0040720468 * $s_;
        $a = 1.9779984951 * $l_ - 2.4285922050 * $m_ + 0.4505937099 * $s_;
        $b = 0.0259040371 * $l_ + 0.7827717662 * $m_ - 0.8086757660 * $s_;

        $c = sqrt($a * $a + $b * $b);
        $h = atan2($b, $a) * 180.0 / M_PI;
        if ($h < 0) {
            $h += 360.0;
        }

        if ($c < 1e-8) {
            $h = 0.0;
        }

        return new OklchTuple($L, $c, $h);
    }

    /**
     * @return array{0: float, 1: float, 2: float}
     */
    private function oklchToLinearSrgb(OklchTuple $tuple): array
    {
        $hRad = $tuple->h * M_PI / 180.0;
        $a = $tuple->c * cos($hRad);
        $b = $tuple->c * sin($hRad);

        $l_ = $tuple->l + 0.3963377774 * $a + 0.2158037573 * $b;
        $m_ = $tuple->l - 0.1055613458 * $a - 0.0638541728 * $b;
        $s_ = $tuple->l - 0.0894841775 * $a - 1.2914855480 * $b;

        $l = $l_ * $l_ * $l_;
        $m = $m_ * $m_ * $m_;
        $s = $s_ * $s_ * $s_;

        $r = +4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s;
        $g = -1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s;
        $bLin = -0.0041960863 * $l - 0.7034186147 * $m + 1.7076147010 * $s;

        return [
            max(0.0, min(1.0, $r)),
            max(0.0, min(1.0, $g)),
            max(0.0, min(1.0, $bLin)),
        ];
    }

    private function srgbToLinear(float $c): float
    {
        return $c <= 0.04045 ? $c / 12.92 : pow(($c + 0.055) / 1.055, 2.4);
    }

    private function linearToSrgb(float $c): float
    {
        $c = max(0.0, min(1.0, $c));

        return $c <= 0.0031308 ? 12.92 * $c : 1.055 * pow($c, 1.0 / 2.4) - 0.055;
    }
}
