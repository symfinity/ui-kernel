<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Http;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Http\ThemeSchemeResponder;
use Symfinity\UiKernel\Theme\ActiveThemeContext;
use Symfinity\UiKernel\Theme\ThemeColorScheme;
use Symfinity\UiKernel\Theme\ThemePreference;
use Symfinity\UiKernel\Theme\ThemePreferenceCookies;
use Symfinity\UiKernel\Theme\ThemePreferenceResolver;
use Symfinity\UiKernel\Theme\ThemeRegistry;
use Symfony\Component\HttpFoundation\Request;

final class ThemeSchemeResponderTest extends TestCase
{
    private ThemeSchemeResponder $responder;

    protected function setUp(): void
    {
        $registry = new ThemeRegistry();
        $resolver = new ThemePreferenceResolver($registry, 'semantic');
        $cookies = new ThemePreferenceCookies();
        $context = new ActiveThemeContext($cookies, $resolver, $registry);
        $this->responder = new ThemeSchemeResponder($context, $resolver, $cookies, new CssGenerator());
    }

    #[Test]
    public function itReturnsResolvedThemePayloadAndSetsSchemeCookie(): void
    {
        $request = Request::create('/');
        $preference = new ThemePreference('semantic', ThemeColorScheme::Dark);

        $response = $this->responder->respond($request, $preference);
        $payload = json_decode((string) $response->getContent(), true);

        self::assertIsArray($payload);
        self::assertSame('semantic-dark', $payload['themeId']);
        self::assertSame('dark', $payload['colorScheme']);
        self::assertSame('dark', $payload['scheme']);
        self::assertSame('semantic', $payload['lineage']);
        self::assertNotSame('', $payload['css']);

        $cookieValues = [];
        foreach ($response->headers->getCookies() as $cookie) {
            $cookieValues[$cookie->getName()] = $cookie->getValue();
        }

        self::assertSame('dark', $cookieValues['symfinity_ui_kernel_scheme'] ?? null);
        self::assertSame('semantic', $cookieValues['symfinity_ui_kernel_lineage'] ?? null);
    }

    #[Test]
    public function itResolvesAutoSchemeFromPatchBodyWhenClientHintHeaderAbsent(): void
    {
        $request = Request::create(
            '/',
            'PATCH',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['scheme' => 'auto', 'systemPrefersDark' => true], \JSON_THROW_ON_ERROR),
        );
        $preference = new ThemePreference('semantic', ThemeColorScheme::Auto);

        $response = $this->responder->respond($request, $preference);
        $payload = json_decode((string) $response->getContent(), true);

        self::assertIsArray($payload);
        self::assertSame('semantic-dark', $payload['themeId']);
        self::assertSame('dark', $payload['colorScheme']);
        self::assertSame('auto', $payload['scheme']);
    }
}
