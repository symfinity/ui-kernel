<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Token\SemanticColourVocabulary;

final class SemanticColourVocabularyTest extends TestCase
{
    #[Test]
    public function builtInSemanticThemeIncludesPlatformMinimum(): void
    {
        $vocabulary = SemanticColourVocabulary::fromBuiltInThemeId('semantic');

        foreach (SemanticColourVocabulary::PLATFORM_MINIMUM as $name) {
            self::assertContains($name, $vocabulary->all(), $name);
        }

        self::assertCount(8, array_intersect(SemanticColourVocabulary::PLATFORM_MINIMUM, $vocabulary->all()));
    }

    #[Test]
    public function defaultNamePrefersPrimary(): void
    {
        $vocabulary = SemanticColourVocabulary::fromBuiltInThemeId('default');

        self::assertSame('primary', $vocabulary->defaultName());
    }
}
