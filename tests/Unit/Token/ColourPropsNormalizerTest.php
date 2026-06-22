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
        self::assertSame('accent', $this->normalizer->normalize('tertiary'));
        self::assertSame('neutral', $this->normalizer->normalize('ghost'));
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
            ['variant' => 'danger', 'submitVariant' => 'accent'],
            'variant',
            'submitVariant',
        );

        self::assertSame('danger', $normalized['variant']);
        self::assertSame('accent', $normalized['submitVariant']);
    }

    #[Test]
    public function normalizeButtonColourMapsGhostToNeutralAppearance(): void
    {
        $normalized = $this->normalizer->normalizeButtonColour('ghost', 'solid');

        self::assertSame('neutral', $normalized['variant']);
        self::assertSame('ghost', $normalized['appearance']);
    }

    #[Test]
    public function normalizeButtonColourPreservesExplicitAppearanceWhenGhostVariant(): void
    {
        $normalized = $this->normalizer->normalizeButtonColour('ghost', 'outline');

        self::assertSame('neutral', $normalized['variant']);
        self::assertSame('outline', $normalized['appearance']);
    }

    #[Test]
    public function normalizeButtonColourMapsOutlineVariantToAppearance(): void
    {
        $normalized = $this->normalizer->normalizeButtonColour('outline', 'solid');

        self::assertSame('primary', $normalized['variant']);
        self::assertSame('outline', $normalized['appearance']);
    }

    #[Test]
    public function tokenKeyUsesSemanticSlug(): void
    {
        self::assertSame('--ui-color-accent', ColourPropsNormalizer::tokenKey('accent'));
        self::assertSame('--ui-color-neutral', ColourPropsNormalizer::tokenKey('neutral'));
        self::assertSame('--ui-color-danger', ColourPropsNormalizer::tokenKey('danger'));
    }
}
