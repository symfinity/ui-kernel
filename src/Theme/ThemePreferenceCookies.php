<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ThemePreferenceCookies
{
    public const SCHEME = 'symfinity_ui_kernel_scheme';

    public const LINEAGE = 'symfinity_ui_kernel_lineage';

    private const TTL = 31536000;

    public function read(Request $request, string $defaultLineage): ThemePreference
    {
        $lineage = $request->cookies->getString(self::LINEAGE);
        if ($lineage === '' || !in_array($lineage, ThemeLineageCatalog::lineages(), true)) {
            $lineage = $defaultLineage;
        }

        $scheme = ThemeColorScheme::tryFromString($request->cookies->getString(self::SCHEME)) ?? ThemeColorScheme::Auto;

        return new ThemePreference($lineage, $scheme);
    }

    /**
     * @return list<Cookie>
     */
    public function create(ThemePreference $preference): array
    {
        $expires = time() + self::TTL;

        return [
            Cookie::create(self::SCHEME, $preference->scheme->value, $expires, '/', null, false, true, false, Cookie::SAMESITE_LAX),
            Cookie::create(self::LINEAGE, $preference->lineage, $expires, '/', null, false, true, false, Cookie::SAMESITE_LAX),
        ];
    }

    public function attach(Response $response, ThemePreference $preference): void
    {
        foreach ($this->create($preference) as $cookie) {
            $response->headers->setCookie($cookie);
        }
    }
}
