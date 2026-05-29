<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Integration;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ShowcaseRouteTest extends WebTestCase
{
    protected static function getKernelClass(): string
    {
        return UiKernelTestKernel::class;
    }

    #[Test]
    public function showcaseReturns200WithDataTheme(): void
    {
        $client = static::createClient();
        $client->request('GET', '/ui-kernel/showcase');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('html[data-theme]');
        self::assertSelectorTextContains('#ui-kernel-theme-label', 'Current flavour:');
        self::assertSelectorExists('[data-ui-role="button"]');
    }

    #[Test]
    public function fixedThemeQueryDisablesCarouselFlag(): void
    {
        $client = static::createClient();
        $client->request('GET', '/ui-kernel/showcase?theme=dark');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('html[data-theme="dark"]');
        self::assertSelectorExists('#ui-kernel-showcase[data-carousel="0"]');
    }

    #[Test]
    public function unknownThemeFallsBackToDefault(): void
    {
        $client = static::createClient();
        $client->request('GET', '/ui-kernel/showcase?theme=unknown');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('html[data-theme="default"]');
    }

    #[Test]
    public function themeCssRouteReturnsScopedVariables(): void
    {
        $client = static::createClient();
        $client->request('GET', '/_ui/theme.css?theme=dark');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('[data-theme="dark"]', $client->getResponse()->getContent());
        self::assertStringContainsString('--ui-color-primary', $client->getResponse()->getContent());
    }
}
