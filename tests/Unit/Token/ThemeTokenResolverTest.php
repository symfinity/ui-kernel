<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Token\FlavourThemeConfig;
use Symfinity\UiKernel\Token\ThemeTokenResolver;
use Symfinity\UiKernel\Token\ThemeTokenSchema;
use Symfinity\UiKernel\Token\UserTokenSet;

final class ThemeTokenResolverTest extends TestCase
{
    #[Test]
    public function itResolvesAllSchemaTwoKeysForSemanticFlavour(): void
    {
        $tokens = (new ThemeTokenResolver())->resolve(FlavourThemeConfig::get('semantic'))->all();

        foreach (ThemeTokenSchema::requiredKeys(ThemeTokenSchema::V2_0) as $key) {
            self::assertArrayHasKey($key, $tokens, $key);
            self::assertNotSame('', $tokens[$key]);
        }
    }

    #[Test]
    public function userTokenOverrideMergesOverFlavour(): void
    {
        $override = new UserTokenSet(['--ui-color-primary' => '#112233']);
        $tokens = (new ThemeTokenResolver())->resolve(
            FlavourThemeConfig::get('semantic'),
            $override,
        )->all();

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

        $override = new UserTokenSet(['--ui-color-warning' => '#112233']);
        $base = (new ThemeTokenResolver())->resolve(FlavourThemeConfig::get('semantic'))->all();
        $override->merge($base, ThemeTokenSchema::V1_0);
    }
}
