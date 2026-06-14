<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Dtcg\DesignSystemLayerRegistry;
use Symfinity\UiKernel\Dtcg\LayerStackBuilder;
use Symfinity\UiKernel\Dtcg\ThemeDtcgResolver;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;
use Symfinity\UiKernel\Token\UserTokenSet;

final class ThemeTokenResolverTest extends TestCase
{
    private ThemeDtcgResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new ThemeDtcgResolver(new LayerStackBuilder(
            new DesignSystemLayerRegistry(DesignSystemLayerRegistry::defaultDirectory()),
        ));
        ThemeCatalog::reset();
    }

    #[Test]
    public function itResolvesAllSchemaOneKeysForSemanticTheme(): void
    {
        $variant = ThemeCatalog::variant('semantic');
        $tokens = $this->resolver->resolve($variant)->all();

        foreach (ThemeTokenSchema::requiredKeys(ThemeTokenSchema::V1_0) as $key) {
            self::assertArrayHasKey($key, $tokens, $key);
            self::assertNotSame('', $tokens[$key]);
        }
    }

    #[Test]
    public function userTokenOverrideMergesOverTheme(): void
    {
        $override = new UserTokenSet(['--ui-color-primary' => '#112233']);
        $tokens = $this->resolver->resolve(ThemeCatalog::variant('semantic'), $override)->all();

        self::assertSame('#112233', $tokens['--ui-color-primary']);
    }

    #[Test]
    public function userTokenRejectsInvalidKeyPrefix(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new UserTokenSet(['not-a-token' => '#000']);
    }

    #[Test]
    public function userTokenMergeRejectsKeysOutsideSchema(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $override = new UserTokenSet(['--ui-not-a-real-token' => '#112233']);
        $base = $this->resolver->resolve(ThemeCatalog::variant('semantic'))->all();
        $override->merge($base, ThemeTokenSchema::V1_0);
    }
}
