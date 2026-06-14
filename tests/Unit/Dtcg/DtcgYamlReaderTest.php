<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Dtcg;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Contract\Token\TokenType;
use Symfinity\UiKernel\Dtcg\DtcgYamlReader;

final class DtcgYamlReaderTest extends TestCase
{
    #[Test]
    public function itParsesYamlIntoTheSameModelShape(): void
    {
        $yaml = <<<'YAML'
        color:
          $type: color
          blue:
            "600": { $value: { colorSpace: oklch, components: [0.55, 0.21, 256] } }
          primary: { $value: "{color.blue.600}" }
        YAML;

        $flat = (new DtcgYamlReader())->fromString($yaml)->flatten();

        self::assertSame(['color.blue.600', 'color.primary'], array_keys($flat));
        self::assertSame(TokenType::Color, $flat['color.primary']->type());
        self::assertTrue($flat['color.primary']->isAlias());
        self::assertSame('color.blue.600', (string) $flat['color.primary']->value()->target());
    }
}
