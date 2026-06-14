<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Contract;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Contract\Token\TokenType;

final class TokenTypeTest extends TestCase
{
    #[Test]
    public function itMapsKnownDtcgTypes(): void
    {
        self::assertSame(TokenType::Color, TokenType::fromDtcg('color'));
        self::assertSame(TokenType::Dimension, TokenType::fromDtcg('dimension'));
        self::assertSame(TokenType::FontFamily, TokenType::fromDtcg('fontFamily'));
        self::assertSame(TokenType::CubicBezier, TokenType::fromDtcg('cubicBezier'));
    }

    #[Test]
    public function itFallsBackToUnknown(): void
    {
        self::assertSame(TokenType::Unknown, TokenType::fromDtcg(null));
        self::assertSame(TokenType::Unknown, TokenType::fromDtcg(''));
        self::assertSame(TokenType::Unknown, TokenType::fromDtcg('mysteryType'));
    }
}
