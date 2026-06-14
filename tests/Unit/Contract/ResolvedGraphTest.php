<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Contract;

use OutOfBoundsException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Contract\Resolver\ResolvedGraph;
use Symfinity\UiKernel\Contract\Token\Token;
use Symfinity\UiKernel\Contract\Token\TokenPath;
use Symfinity\UiKernel\Contract\Token\TokenType;

final class ResolvedGraphTest extends TestCase
{
    #[Test]
    public function itExposesTokensAndSignature(): void
    {
        $graph = new ResolvedGraph([
            'color.primary' => new Token(TokenPath::fromString('color.primary'), TokenType::Color, 'p'),
            'space.4' => new Token(TokenPath::fromString('space.4'), TokenType::Dimension, '1rem'),
        ], 'sig-123');

        self::assertTrue($graph->has('color.primary'));
        self::assertTrue($graph->has(TokenPath::fromString('space.4')));
        self::assertFalse($graph->has('color.ghost'));
        self::assertSame('p', $graph->get('color.primary')->value());
        self::assertSame('sig-123', $graph->layerSignature());
        self::assertCount(2, $graph->all());
    }

    #[Test]
    public function getThrowsForMissingPath(): void
    {
        $graph = new ResolvedGraph([], 'sig');

        $this->expectException(OutOfBoundsException::class);

        $graph->get('color.primary');
    }

    #[Test]
    public function semanticColorsListsTwoSegmentColorNamesOnly(): void
    {
        $graph = new ResolvedGraph([
            'color.primary' => new Token(TokenPath::fromString('color.primary'), TokenType::Color, 'p'),
            'color.accent' => new Token(TokenPath::fromString('color.accent'), TokenType::Color, 'a'),
            'color.blue.600' => new Token(TokenPath::fromString('color.blue.600'), TokenType::Color, 'b'),
            'space.4' => new Token(TokenPath::fromString('space.4'), TokenType::Dimension, '1rem'),
        ], 'sig');

        self::assertSame(['primary', 'accent'], $graph->semanticColors());
    }
}
