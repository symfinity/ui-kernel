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
        yield 'mono warm' => ['mono.warm.500'];
        yield 'mono with alpha' => ['mono.cool.950@40'];
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
        yield 'suffix s' => ['mono.cool.200s'];
        yield 'non-contract level 0' => ['mono.warm.0'];
        yield 'non-contract level 975' => ['mono.warm.975'];
        yield 'invalid alpha' => ['blue.500@33'];
    }
}
