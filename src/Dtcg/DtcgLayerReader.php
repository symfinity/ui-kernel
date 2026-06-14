<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Dtcg;

use Symfinity\UiKernel\Contract\Token\AliasReference;

/**
 * Reads semantic colour refs from a variant DTCG layer file (077 tooling/tests).
 */
final class DtcgLayerReader
{
    /** @var array<string, string> dtcg path suffix => flat role key */
    private const PATH_TO_ROLE = [
        'color.primary' => 'primary',
        'color.secondary' => 'secondary',
        'color.tertiary' => 'tertiary',
        'color.surface.base' => 'surface',
        'color.surface.elevated' => 'surface_elevated',
        'color.text.default' => 'text',
        'color.text.muted' => 'text_muted',
        'color.border.default' => 'border',
        'color.danger' => 'danger',
        'color.success' => 'success',
        'color.warning' => 'warning',
        'color.info' => 'info',
        'color.focus' => 'focus',
        'color.skeleton.base' => 'skeleton_base',
        'color.skeleton.shine' => 'skeleton_shine',
    ];

    /**
     * @return array<string, string> role => palette ref or concrete CSS (alpha refs)
     */
    public function colorRefsFromLayer(string $layerPath): array
    {
        $document = (new DtcgYamlReader())->read($layerPath);
        $refs = [];

        foreach (self::PATH_TO_ROLE as $path => $role) {
            $flat = $document->flatten();
            if (!isset($flat[$path])) {
                continue;
            }
            $token = $flat[$path];
            if ($token->isAlias()) {
                /** @var AliasReference $alias */
                $alias = $token->value();
                $refs[$role] = self::paletteRefFromDtcgPath((string) $alias->target());

                continue;
            }

            $value = $token->value();
            if (!\is_string($value) || !str_starts_with($value, '{')) {
                continue;
            }

            $refs[$role] = self::paletteRefFromValue($value);
        }

        return $refs;
    }

    private static function paletteRefFromDtcgPath(string $path): string
    {
        if (preg_match('/^color\.mono\.([a-z]+)\.(\d+)$/', $path, $matches) === 1) {
            return sprintf('mono.%s.%s', $matches[1], $matches[2]);
        }

        if (preg_match('/^color\.([a-z]+)\.(\d+)$/', $path, $matches) === 1) {
            return sprintf('%s.%s', $matches[1], $matches[2]);
        }

        return $path;
    }

    private static function paletteRefFromValue(string $value): string
    {
        if (preg_match('/^\{color\.mono\.([a-z]+)\.(\d+)\}$/', $value, $matches) === 1) {
            return sprintf('mono.%s.%s', $matches[1], $matches[2]);
        }

        if (preg_match('/^\{color\.([a-z]+)\.(\d+)\}$/', $value, $matches) === 1) {
            return sprintf('%s.%s', $matches[1], $matches[2]);
        }

        return $value;
    }
}
