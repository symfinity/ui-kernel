<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Theme;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Theme\ThemeColorScheme;
use Symfinity\UiKernel\Theme\ThemePreference;
use Symfinity\UiKernel\Theme\ThemePreferenceCookies;

final class ThemePreferenceCookiesTest extends TestCase
{
    #[Test]
    public function itCreatesCookiesWithFutureExpiry(): void
    {
        $cookies = new ThemePreferenceCookies();
        $before = time();

        $created = $cookies->create(new ThemePreference('default', ThemeColorScheme::Dark));

        self::assertCount(2, $created);
        foreach ($created as $cookie) {
            self::assertGreaterThan($before, $cookie->getExpiresTime());
            self::assertGreaterThanOrEqual(31536000 - 5, $cookie->getExpiresTime() - $before);
        }
    }

    #[Test]
    public function itFallsBackToDefaultLineageWhenCookieLineageUnknown(): void
    {
        $cookies = new ThemePreferenceCookies();
        $request = \Symfony\Component\HttpFoundation\Request::create('/');
        $request->cookies->set(ThemePreferenceCookies::LINEAGE, 'removed-lineage');
        $request->cookies->set(ThemePreferenceCookies::SCHEME, ThemeColorScheme::Dark->value);

        $preference = $cookies->read($request, 'semantic');

        self::assertSame('semantic', $preference->lineage);
        self::assertSame(ThemeColorScheme::Dark, $preference->scheme);
    }

    #[Test]
    public function itFallsBackToAutoWhenCookieSchemeInvalid(): void
    {
        $cookies = new ThemePreferenceCookies();
        $request = \Symfony\Component\HttpFoundation\Request::create('/');
        $request->cookies->set(ThemePreferenceCookies::LINEAGE, 'utility');
        $request->cookies->set(ThemePreferenceCookies::SCHEME, 'not-a-scheme');

        $preference = $cookies->read($request, 'default');

        self::assertSame('utility', $preference->lineage);
        self::assertSame(ThemeColorScheme::Auto, $preference->scheme);
    }
}
