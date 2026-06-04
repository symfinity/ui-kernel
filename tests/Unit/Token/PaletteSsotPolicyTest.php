<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssCacheKeyPolicy;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Profile\SystemProfile;
use Symfinity\UiKernel\Token\ButtonStateDerivation;
use Symfinity\UiKernel\Token\ButtonVariantMap;
use Symfinity\UiKernel\Token\CanonicalTokenPolicy;
use Symfinity\UiKernel\Token\ThemeConfig;
use Symfinity\UiKernel\Token\PresetCycleGuard;
use Symfinity\UiKernel\Token\RegistryResolutionPolicy;
use Symfinity\UiKernel\Token\ThemeErrorCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;
use Symfinity\UiKernel\Token\TokenValueValidator;
use Symfinity\UiKernel\Token\UiKernelThemeException;
use Symfinity\UiKernel\Token\UserTokenSet;

final class PaletteSsotPolicyTest extends TestCase
{
    #[Test]
    public function semanticVariantsMapToCanonicalTokenKeys(): void
    {
        self::assertSame('--ui-color-primary', ButtonVariantMap::semanticTokenKey('primary'));
        self::assertSame('--ui-color-danger', ButtonVariantMap::semanticTokenKey('destructive'));
        self::assertSame('--ui-color-info', ButtonVariantMap::semanticTokenKey('info'));
    }

    #[Test]
    public function unknownVariantThrowsStableErrorCode(): void
    {
        try {
            ButtonVariantMap::semanticTokenKey('neon');
            self::fail('Expected UiKernelThemeException');
        } catch (UiKernelThemeException $e) {
            self::assertSame(ThemeErrorCatalog::UNKNOWN_TOKEN_KEY, $e->errorCode);
        }
    }

    #[Test]
    public function activeIsStrongerThanHoverForAllSemanticVariantsAcrossThemes(): void
    {
        foreach (['default', 'default-dark', 'semantic', 'semantic-dark', 'utility', 'utility-dark'] as $themeId) {
            $tokens = ThemeCatalog::get($themeId)->tokens()->all();

            foreach (ButtonVariantMap::SEMANTIC_VARIANTS as $variant) {
                $tokenKey = ButtonVariantMap::semanticTokenKey($variant);
                $baseHex = $tokens[$tokenKey];
                self::assertTrue(
                    ButtonStateDerivation::isActiveStrongerThanHover($baseHex),
                    sprintf('%s/%s failed active>hover for %s', $themeId, $variant, $baseHex),
                );
            }
        }
    }

    #[Test]
    public function forbiddenLegacyAliasesAreRejected(): void
    {
        foreach (array_keys(CanonicalTokenPolicy::FORBIDDEN_ALIASES) as $alias) {
            try {
                CanonicalTokenPolicy::assertCanonicalKey($alias);
                self::fail('Expected forbidden alias rejection for ' . $alias);
            } catch (UiKernelThemeException $e) {
                self::assertSame(ThemeErrorCatalog::FORBIDDEN_TOKEN_ALIAS, $e->errorCode);
            }
        }
    }

    #[Test]
    public function userTokenSetRejectsForbiddenAlias(): void
    {
        $this->expectException(UiKernelThemeException::class);

        new UserTokenSet(['--ui-color-focus-ring' => '#336699']);
    }

    #[Test]
    public function tokenValueValidatorRejectsUnsafeCss(): void
    {
        try {
            TokenValueValidator::assertValid('--ui-color-primary', 'url(javascript:alert(1))');
            self::fail('Expected unsafe CSS rejection');
        } catch (UiKernelThemeException $e) {
            self::assertSame(ThemeErrorCatalog::INVALID_TOKEN_VALUE, $e->errorCode);
        }
    }

    #[Test]
    public function registryCollisionPolicySortsByPriorityThenId(): void
    {
        $sorted = RegistryResolutionPolicy::sortedProviderIds([
            ['id' => 'beta', 'priority' => 10],
            ['id' => 'alpha', 'priority' => 20],
            ['id' => 'gamma', 'priority' => 10],
        ]);

        self::assertSame(['alpha', 'beta', 'gamma'], $sorted);
    }

    #[Test]
    public function duplicateProviderIdsThrowRegistryCollision(): void
    {
        try {
            RegistryResolutionPolicy::assertNoDuplicateProviderIds(['a', 'b', 'a']);
            self::fail('Expected registry collision');
        } catch (UiKernelThemeException $e) {
            self::assertSame(ThemeErrorCatalog::REGISTRY_COLLISION, $e->errorCode);
        }
    }

    #[Test]
    public function duplicateThemeIdThrowsStableErrorCode(): void
    {
        try {
            RegistryResolutionPolicy::assertUniqueThemeId('semantic', true);
            self::fail('Expected duplicate theme rejection');
        } catch (UiKernelThemeException $e) {
            self::assertSame(ThemeErrorCatalog::DUPLICATE_THEME_ID, $e->errorCode);
        }
    }

    #[Test]
    public function lineageCycleGuardDetectsCycles(): void
    {
        try {
            PresetCycleGuard::assertAcyclic(['child', 'parent', 'child']);
            self::fail('Expected preset cycle rejection');
        } catch (UiKernelThemeException $e) {
            self::assertSame(ThemeErrorCatalog::PRESET_CYCLE, $e->errorCode);
        }
    }

    #[Test]
    public function cacheKeyFingerprintChangesOnLineageMutation(): void
    {
        $profile = SystemProfile::chameleonDefault();
        $semantic = ThemeConfig::get('semantic');
        $utility = ThemeConfig::get('utility');

        $partsA = CssCacheKeyPolicy::parts(
            'semantic',
            'user-hash',
            ThemeTokenSchema::V1_0,
            $semantic->presetHash(),
            $profile,
        );
        $partsB = CssCacheKeyPolicy::parts(
            'utility',
            'user-hash',
            ThemeTokenSchema::V1_0,
            $utility->presetHash(),
            $profile,
        );

        self::assertNotSame(
            CssCacheKeyPolicy::fingerprint($partsA),
            CssCacheKeyPolicy::fingerprint($partsB),
        );
    }

    #[Test]
    public function cacheKeyFingerprintChangesOnUserTokenHash(): void
    {
        $profile = SystemProfile::chameleonDefault();
        $preset = ThemeConfig::get('semantic')->presetHash();

        $partsA = CssGenerator::cacheKeyParts('semantic', 'hash-a', ThemeTokenSchema::V1_0, $profile, $preset);
        $partsB = CssGenerator::cacheKeyParts('semantic', 'hash-b', ThemeTokenSchema::V1_0, $profile, $preset);

        self::assertNotSame(
            CssCacheKeyPolicy::fingerprint($partsA),
            CssCacheKeyPolicy::fingerprint($partsB),
        );
    }

    #[Test]
    public function generatedCssUsesCentralizedButtonHoverAndActiveDerivation(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V1_0);

        self::assertStringContainsString(
            ':hover:not([disabled]):not([aria-disabled="true"])',
            $css,
        );
        self::assertStringContainsString(
            'color-mix(in srgb, var(--ui-color-primary)',
            $css,
        );
        self::assertStringContainsString(
            '[data-ui-role="button"][data-ui-state="loading"]',
            $css,
        );
        self::assertStringContainsString(
            'var(--ui-color-focus)',
            $css,
        );
    }
}
