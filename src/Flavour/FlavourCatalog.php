<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Flavour;

use Symfinity\UiKernel\Token\FlavourThemeConfig;
use Symfinity\UiKernel\Token\ThemeTokenResolver;
use Symfinity\UiKernel\Token\UserTokenSet;

/**
 * SSOT for all built-in theme flavours (ids, labels, palette refs, layout lineage).
 *
 * @see docs/theme-flavours.md
 */
final class FlavourCatalog
{
    /** @var list<Flavour>|null */
    private static ?array $defaultFlavours = null;

    /**
     * @return list<Flavour>
     */
    public static function all(?ThemeTokenResolver $resolver = null, ?UserTokenSet $userTokens = null): array
    {
        $useCache = $resolver === null && ($userTokens === null || $userTokens->isEmpty());

        if ($useCache && self::$defaultFlavours !== null) {
            return self::$defaultFlavours;
        }

        $resolver ??= new ThemeTokenResolver();
        $userTokens ??= new UserTokenSet();

        $built = [];
        foreach (FlavourThemeConfig::all() as $config) {
            $built[] = DefinedFlavour::fromConfig($config, $resolver, $userTokens);
        }

        if ($useCache) {
            self::$defaultFlavours = $built;
        }

        return $built;
    }

    public static function get(string $id, ?ThemeTokenResolver $resolver = null, ?UserTokenSet $userTokens = null): Flavour
    {
        foreach (self::all($resolver, $userTokens) as $flavour) {
            if ($flavour->id() === $id) {
                return $flavour;
            }
        }

        throw new \InvalidArgumentException(sprintf('Unknown flavour id "%s".', $id));
    }
}
