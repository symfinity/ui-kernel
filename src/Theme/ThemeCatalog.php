<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

use Symfinity\UiKernel\Token\ThemeConfig;
use Symfinity\UiKernel\Token\ThemeTokenResolver;
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

    /**
     * @return list<Theme>
     */
    public static function all(?ThemeTokenResolver $resolver = null, ?UserTokenSet $userTokens = null): array
    {
        $useCache = $resolver === null && ($userTokens === null || $userTokens->isEmpty());

        if ($useCache && self::$defaultThemes !== null) {
            return self::$defaultThemes;
        }

        $resolver ??= new ThemeTokenResolver();
        $userTokens ??= new UserTokenSet();

        $built = [];
        foreach (ThemeConfig::all() as $config) {
            $built[] = DefinedTheme::fromConfig($config, $resolver, $userTokens);
        }

        if ($useCache) {
            self::$defaultThemes = $built;
        }

        return $built;
    }

    public static function get(string $id, ?ThemeTokenResolver $resolver = null, ?UserTokenSet $userTokens = null): Theme
    {
        foreach (self::all($resolver, $userTokens) as $theme) {
            if ($theme->id() === $id) {
                return $theme;
            }
        }

        throw new \InvalidArgumentException(sprintf('Unknown theme id "%s".', $id));
    }
}
