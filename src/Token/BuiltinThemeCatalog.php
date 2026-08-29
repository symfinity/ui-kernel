<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use Symfinity\UiKernel\Dtcg\BuiltinDtcgThemeCatalog;
use Symfinity\UiKernel\Dtcg\DtcgYamlReader;

/**
 * Built-in UI Kernel themes — legacy array facade over DTCG on-disk layout (077).
 *
 * @deprecated Prefer {@see BuiltinDtcgThemeCatalog} for new code.
 */
final class BuiltinThemeCatalog
{
    /**
     * @var list<array{
     *     id: string,
     *     label: string,
     *     layout: string,
     *     tone: string,
     *     colors: array<string, string>,
     *     hue_base: array<string, float>,
     *     mono_tones: array<string, array{hue: float, saturation: float}>,
     *     tokens: array<string, string>,
     *     lineage?: string,
     *     scroll_motion?: bool,
     *     backdrop_blur?: string
     * }>|null
     */
    private static ?array $themes = null;

    /** @var array<string, string>|null */
    private static ?array $lineageDonors = null;

    /**
     * @return list<array{
     *     id: string,
     *     label: string,
     *     layout: string,
     *     tone: string,
     *     colors: array<string, string>,
     *     hue_base: array<string, float>,
     *     mono_tones: array<string, array{hue: float, saturation: float}>,
     *     tokens: array<string, string>,
     *     lineage?: string,
     *     scroll_motion?: bool,
     *     backdrop_blur?: string
     * }>
     */
    public static function themes(): array
    {
        if (self::$themes !== null) {
            return self::$themes;
        }

        self::load();

        return self::$themes ?? throw new \LogicException('Built-in themes failed to load.');
    }

    /**
     * @return array<string, string>
     */
    public static function lineageDonors(): array
    {
        if (self::$lineageDonors !== null) {
            return self::$lineageDonors;
        }

        self::load();

        return self::$lineageDonors ?? throw new \LogicException('Built-in theme lineage donors failed to load.');
    }

    public static function reset(): void
    {
        self::$themes = null;
        self::$lineageDonors = null;
        BuiltinDtcgThemeCatalog::reset();
    }

    private static function load(): void
    {
        $catalog = new BuiltinDtcgThemeCatalog(BuiltinDtcgThemeCatalog::defaultDirectory());
        $themes = [];
        foreach ($catalog->all() as $variant) {
            $palette = $variant->paletteDefinition();
            $themes[] = [
                'id' => $variant->id(),
                'label' => $variant->label(),
                'layout' => $variant->layout()->name,
                'tone' => $variant->tone()->value,
                'colors' => [],
                'hue_base' => $palette['hue_base'],
                'mono_tones' => $palette['mono_tones'],
                'hue_chroma' => $palette['hue_chroma'],
                'scale_anchors' => $palette['scale_anchors'],
                'tokens' => self::shortTokensFromLayer($variant->layerPath()),
                'lineage' => $variant->lineage(),
                'scroll_motion' => $variant->scrollMotion(),
                'backdrop_blur' => $variant->backdropBlur(),
            ];
        }

        self::$themes = $themes;
        self::$lineageDonors = $catalog->lineageDonors();
    }

    /**
     * @return array<string, string> short token keys from a variant DTCG layer (appearance only)
     */
    private static function shortTokensFromLayer(string $layerPath): array
    {
        $document = (new DtcgYamlReader())->read($layerPath);
        $short = [];
        foreach ($document->flatten() as $path => $token) {
            if (str_starts_with($path, 'color.')) {
                continue;
            }
            $cssKey = '--ui-' . str_replace('.', '-', $path);
            $shortKey = ThemeTokenMap::cssVarToShortKey($cssKey);
            $value = $token->value();
            if (\is_string($value)) {
                $short[$shortKey] = $value;
            }
        }

        return $short;
    }
}
