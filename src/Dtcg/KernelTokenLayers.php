<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Dtcg;

use Symfinity\UiKernel\Contract\Layer\LayerRole;
use Symfinity\UiKernel\Contract\Layer\TokenLayer;
use Symfinity\UiKernel\Contract\Token\Token;
use Symfinity\UiKernel\Contract\Token\TokenPath;
use Symfinity\UiKernel\Contract\Token\TokenType;
use Symfinity\UiKernel\Theme\Theme;

/**
 * Reproduces today's kernel tokens as DTCG layers so the resolver feeds the existing
 * CSS emitter with output parity (076 US1).
 *
 * The `--ui-*` custom-property key maps to a dotted DTCG path via a dash/dot bijection,
 * matching {@see \Symfinity\UiKernel\Css\CssVariableSet::cssKey()} in reverse.
 */
final class KernelTokenLayers
{
    private const PREFIX = '--ui-';

    /**
     * Build a single base-role layer holding a theme's full resolved token set.
     */
    public function baseLayerForTheme(Theme $theme): TokenLayer
    {
        return $this->layerFromCssVariables('base:' . $theme->id(), LayerRole::Base, $theme->tokens()->all());
    }

    /**
     * Build a layer from a `--ui-*` custom-property map (string values, opaque type).
     *
     * @param array<string, string> $variables
     */
    public function layerFromCssVariables(string $id, LayerRole $role, array $variables): TokenLayer
    {
        $tokens = [];
        foreach ($variables as $cssKey => $value) {
            $path = self::pathFromCssKey($cssKey);
            $tokens[(string) $path] = new Token($path, TokenType::Unknown, $value);
        }

        return new TokenLayer($id, $role, $tokens);
    }

    public static function pathFromCssKey(string $cssKey): TokenPath
    {
        $body = str_starts_with($cssKey, self::PREFIX) ? substr($cssKey, \strlen(self::PREFIX)) : $cssKey;

        return TokenPath::fromString(str_replace('-', '.', $body));
    }
}
