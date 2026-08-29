<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Css;

final class CascadeLayerWrap
{
    public static function wrap(string $layerName, string $css): string
    {
        $css = trim($css);
        if ($css === '') {
            return '';
        }

        if (self::isAlreadyWrapped($layerName, $css)) {
            return $css;
        }

        return "@layer {$layerName} {\n{$css}\n}";
    }

    public static function isAlreadyWrapped(string $layerName, string $css): bool
    {
        return str_starts_with(ltrim($css), "@layer {$layerName}");
    }
}
