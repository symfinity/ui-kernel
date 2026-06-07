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
}
