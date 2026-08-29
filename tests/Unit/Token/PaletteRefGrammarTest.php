<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Token\PaletteRefGrammar;

final class PaletteRefGrammarTest extends TestCase
{
    #[Test]
    #[DataProvider('validRefsProvider')]
    public function itAcceptsValidRefs(string $ref): void
    {
        PaletteRefGrammar::assertValid($ref);
        $this->addToAssertionCount(1);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validRefsProvider(): iterable
    {
        yield 'mono warm' => ['mono.stone.500'];
        yield 'mono with alpha' => ['mono.slate.950@40'];
        yield 'hue' => ['blue.600'];
        yield 'hue alpha' => ['red.500@25'];
    }

    #[Test]
    #[DataProvider('invalidRefsProvider')]
    public function itRejectsForbiddenRefs(string $ref): void
    {
        $this->expectException(InvalidArgumentException::class);
        PaletteRefGrammar::assertValid($ref);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidRefsProvider(): iterable
    {
        yield 'suffix u' => ['red.500u'];
        yield 'suffix t' => ['sky.500t'];
        yield 'suffix s' => ['mono.slate.200s'];
        yield 'non-contract level 0' => ['mono.stone.0'];
        yield 'non-contract level 975' => ['mono.stone.975'];
        yield 'invalid alpha' => ['blue.500@33'];
        yield 'legacy cool' => ['mono.cool.500'];
        yield 'legacy pure' => ['mono.pure.100'];
    }
}
