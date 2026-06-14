<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

use Symfinity\UiKernel\Dtcg\BuiltinDtcgThemeCatalog;
use Symfinity\UiKernel\Dtcg\BuiltinThemeVariant;
use Symfinity\UiKernel\Dtcg\ThemeDtcgResolver;
use Symfinity\UiKernel\Token\DesignTokenSet;
use Symfinity\UiKernel\Token\UserTokenSet;

/**
 * SSOT for all built-in themes (ids, labels, palette refs, layout lineage).
 *
 * @see docs/themes.md
 */
final class ThemeCatalog
{
    /** @var list<Theme>|null */
    private static ?array $defaultThemes = null;

    private static ?BuiltinDtcgThemeCatalog $dtcgCatalog = null;

    private static ?ThemeDtcgResolver $dtcgResolver = null;

    /**
     * @return list<Theme>
     */
    public static function all(?ThemeDtcgResolver $resolver = null, ?UserTokenSet $userTokens = null): array
    {
        $useCache = $resolver === null && ($userTokens === null || $userTokens->isEmpty());

        if ($useCache && self::$defaultThemes !== null) {
            return self::$defaultThemes;
        }

        $resolver ??= self::dtcgResolver();
        $userTokens ??= new UserTokenSet();

        $built = [];
        foreach (self::dtcgCatalog()->all() as $variant) {
            $built[] = DefinedTheme::fromVariant($variant, $resolver, $userTokens);
        }

        if ($useCache) {
            self::$defaultThemes = $built;
        }

        return $built;
    }

    public static function get(string $id, ?ThemeDtcgResolver $resolver = null, ?UserTokenSet $userTokens = null): Theme
    {
        foreach (self::all($resolver, $userTokens) as $theme) {
            if ($theme->id() === $id) {
                return $theme;
            }
        }

        throw new \InvalidArgumentException(sprintf('Unknown theme id "%s".', $id));
    }

    public static function variant(string $id): BuiltinThemeVariant
    {
        return self::dtcgCatalog()->get($id);
    }

    public static function reset(): void
    {
        self::$defaultThemes = null;
        BuiltinDtcgThemeCatalog::reset();
    }

    private static function dtcgCatalog(): BuiltinDtcgThemeCatalog
    {
        return self::$dtcgCatalog ??= new BuiltinDtcgThemeCatalog(BuiltinDtcgThemeCatalog::defaultDirectory());
    }

    private static function dtcgResolver(): ThemeDtcgResolver
    {
        return self::$dtcgResolver ??= new ThemeDtcgResolver(
            new \Symfinity\UiKernel\Dtcg\LayerStackBuilder(
                new \Symfinity\UiKernel\Dtcg\DesignSystemLayerRegistry(
                    \Symfinity\UiKernel\Dtcg\DesignSystemLayerRegistry::defaultDirectory(),
                ),
            ),
        );
    }
}
