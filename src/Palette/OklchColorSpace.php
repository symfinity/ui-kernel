<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Palette;

use InvalidArgumentException;

/**
 * OKLCH colour space utilities — parse, ΔE, in-gamut chroma cap, sRGB hex.
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

        $alpha = isset($matches[4]) ? (float) $matches[4] : null;

        return new OklchTuple((float) $matches[1], (float) $matches[2], (float) $matches[3], $alpha);
    }

    public function toSrgb(OklchTuple $tuple): string
    {
        [$r, $g, $b] = $this->oklchToLinearSrgb($this->capToSrgbGamut($tuple));

        return sprintf(
            '#%02x%02x%02x',
            (int) round($this->linearToSrgb($r) * 255),
            (int) round($this->linearToSrgb($g) * 255),
            (int) round($this->linearToSrgb($b) * 255),
        );
    }

    public function toCss(OklchTuple $tuple): string
    {
        $mapped = $this->capToSrgbGamut($tuple);
        $l = self::formatCssNumber($mapped->l);
        $c = self::formatCssNumber($mapped->c);
        $h = self::formatCssNumber($mapped->h, 2);

        if ($mapped->alpha !== null) {
            $alpha = self::formatCssNumber($mapped->alpha);

            return sprintf('oklch(%s %s %s / %s)', $l, $c, $h, $alpha);
        }

        return sprintf('oklch(%s %s %s)', $l, $c, $h);
    }

    /**
     * Parse oklch() CSS or #hex into a tuple (derivatives + P3 boost).
     */
    public function parseColor(string $css): OklchTuple
    {
        $css = trim($css);

        if (str_starts_with(strtolower($css), 'oklch(')) {
            return $this->parse($css);
        }

        return $this->fromHex($css);
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
     * Largest OKLCH chroma for (L, H) that stays inside sRGB — CSS Color 4 gamut-mapping input.
     */
    public function maxInGamutChroma(float $l, float $h, float $upperBound = 0.4): float
    {
        $upperBound = max(0.0, $upperBound);

        if (!$this->isInSrgbGamut(new OklchTuple($l, 0.0, $h))) {
            return 0.0;
        }

        if ($upperBound <= 0.0) {
            return 0.0;
        }

        if (!$this->isInSrgbGamut(new OklchTuple($l, $upperBound, $h))) {
            return $this->gamutMap(new OklchTuple($l, $upperBound, $h))->c;
        }

        return $upperBound;
    }

    public function capToSrgbGamut(OklchTuple $tuple): OklchTuple
    {
        if ($tuple->c <= 0.0 || $this->isInSrgbGamut($tuple)) {
            return $tuple;
        }

        return $this->gamutMap($tuple);
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

    private function gamutMap(OklchTuple $tuple): OklchTuple
    {
        if ($tuple->c <= 0.0 || $this->isInSrgbGamut($tuple)) {
            return $tuple;
        }

        $low = 0.0;
        $high = $tuple->c;

        for ($i = 0; $i < 24; ++$i) {
            $mid = ($low + $high) / 2.0;
            $candidate = new OklchTuple($tuple->l, $mid, $tuple->h, $tuple->alpha);
            if ($this->isInSrgbGamut($candidate)) {
                $low = $mid;
            } else {
                $high = $mid;
            }
        }

        return new OklchTuple($tuple->l, $low, $tuple->h, $tuple->alpha);
    }

    private function isInSrgbGamut(OklchTuple $tuple): bool
    {
        [$r, $g, $b] = $this->oklchToLinearSrgbUnclamped($tuple);

        return $r >= 0.0 && $r <= 1.0
            && $g >= 0.0 && $g <= 1.0
            && $b >= 0.0 && $b <= 1.0;
    }

    /**
     * @return array{0: float, 1: float, 2: float}
     */
    private function oklchToLinearSrgb(OklchTuple $tuple): array
    {
        return $this->clampLinearSrgb($this->oklchToLinearSrgbUnclamped($tuple));
    }

    /**
     * @return array{0: float, 1: float, 2: float}
     */
    private function oklchToLinearSrgbUnclamped(OklchTuple $tuple): array
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

        return [
            +4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s,
            -1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s,
            -0.0041960863 * $l - 0.7034186147 * $m + 1.7076147010 * $s,
        ];
    }

    /**
     * @param array{0: float, 1: float, 2: float} $linear
     *
     * @return array{0: float, 1: float, 2: float}
     */
    private function clampLinearSrgb(array $linear): array
    {
        return [
            max(0.0, min(1.0, $linear[0])),
            max(0.0, min(1.0, $linear[1])),
            max(0.0, min(1.0, $linear[2])),
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

    private static function formatCssNumber(float $value, int $precision = 4): string
    {
        $formatted = rtrim(rtrim(sprintf('%.' . $precision . 'f', $value), '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }
}
