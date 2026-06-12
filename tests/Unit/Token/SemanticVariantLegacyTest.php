<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Token\SemanticVariant;

final class SemanticVariantLegacyTest extends TestCase
{
    #[Test]
    public function normalizeColourPropsCoercesInvalidValuesToPrimary(): void
    {
        $normalized = SemanticVariant::normalizeColourProps(
            ['variant' => 'default', 'submitVariant' => 'destructive'],
            'variant',
            'submitVariant',
        );

        self::assertSame('primary', $normalized['variant']);
        self::assertSame('primary', $normalized['submitVariant']);
    }

    #[Test]
    public function normalizeColourPropsPreservesValidVariants(): void
    {
        $normalized = SemanticVariant::normalizeColourProps(
            ['variant' => 'danger', 'submitVariant' => 'ghost'],
            'variant',
            'submitVariant',
        );

        self::assertSame('danger', $normalized['variant']);
        self::assertSame('ghost', $normalized['submitVariant']);
    }
}
