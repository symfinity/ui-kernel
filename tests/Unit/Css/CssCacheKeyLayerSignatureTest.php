<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssCacheKeyPolicy;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Profile\SystemProfile;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

/**
 * Cache invalidation on layer change (076 FR-013 / T018).
 */
final class CssCacheKeyLayerSignatureTest extends TestCase
{
    #[Test]
    public function partsIncludeLayerSignature(): void
    {
        $profile = SystemProfile::defaultProfile();
        $parts = CssCacheKeyPolicy::parts('semantic', 'hash', ThemeTokenSchema::V1_0, '', $profile, 'sig-layers');

        self::assertArrayHasKey('layerSignature', $parts);
        self::assertSame('sig-layers', $parts['layerSignature']);
    }

    #[Test]
    public function differentLayerSignatureChangesFingerprint(): void
    {
        $profile = SystemProfile::defaultProfile();

        $a = CssGenerator::cacheKeyParts('semantic', 'hash', ThemeTokenSchema::V1_0, $profile, '', 'sig-a');
        $b = CssGenerator::cacheKeyParts('semantic', 'hash', ThemeTokenSchema::V1_0, $profile, '', 'sig-b');

        self::assertNotSame(
            CssCacheKeyPolicy::fingerprint($a),
            CssCacheKeyPolicy::fingerprint($b),
        );
    }

    #[Test]
    public function sameInputsRemainStable(): void
    {
        $profile = SystemProfile::defaultProfile();

        $a = CssGenerator::cacheKeyParts('semantic', 'hash', ThemeTokenSchema::V1_0, $profile, '', 'sig-a');
        $b = CssGenerator::cacheKeyParts('semantic', 'hash', ThemeTokenSchema::V1_0, $profile, '', 'sig-a');

        self::assertSame(
            CssCacheKeyPolicy::fingerprint($a),
            CssCacheKeyPolicy::fingerprint($b),
        );
    }
}
