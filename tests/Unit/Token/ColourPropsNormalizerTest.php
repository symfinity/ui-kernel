<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Token\ColourPropsNormalizer;
use Symfinity\UiKernel\Token\SemanticColourVocabulary;

final class ColourPropsNormalizerTest extends TestCase
{
    private ColourPropsNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = ColourPropsNormalizer::withBuiltInTheme('semantic');
    }

    #[Test]
    public function normalizeCoercesLegacyAliases(): void
    {
        self::assertSame('primary', $this->normalizer->normalize('default'));
        self::assertSame('primary', $this->normalizer->normalize(''));
        self::assertSame('danger', $this->normalizer->normalize('destructive'));
    }

    #[Test]
    public function normalizeColourPropsCoercesInvalidValuesToPrimary(): void
    {
        $normalized = $this->normalizer->normalizeColourProps(
            ['variant' => 'default', 'submitVariant' => 'destructive'],
            'variant',
            'submitVariant',
        );

        self::assertSame('primary', $normalized['variant']);
        self::assertSame('danger', $normalized['submitVariant']);
    }

    #[Test]
    public function normalizeColourPropsPreservesValidVariants(): void
    {
        $normalized = $this->normalizer->normalizeColourProps(
            ['variant' => 'danger', 'submitVariant' => 'ghost'],
            'variant',
            'submitVariant',
        );

        self::assertSame('danger', $normalized['variant']);
        self::assertSame('ghost', $normalized['submitVariant']);
    }

    #[Test]
    public function ghostMapsToMutedTextToken(): void
    {
        self::assertSame('--ui-color-text-muted', ColourPropsNormalizer::tokenKey('ghost'));
        self::assertSame('--ui-color-danger', ColourPropsNormalizer::tokenKey('danger'));
    }
}
