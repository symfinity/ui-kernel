<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Dtcg;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Contract\Token\AliasReference;
use Symfinity\UiKernel\Contract\Token\TokenType;
use Symfinity\UiKernel\Dtcg\DtcgJsonReader;

final class DtcgJsonReaderTest extends TestCase
{
    #[Test]
    public function itParsesGroupsTokensAliasesAndInheritedType(): void
    {
        $json = <<<'JSON'
        {
          "color": {
            "$type": "color",
            "blue": { "600": { "$value": { "colorSpace": "oklch", "components": [0.55, 0.21, 256] } } },
            "primary": { "$value": "{color.blue.600}", "$description": "brand" }
          },
          "space": {
            "4": { "$type": "dimension", "$value": "1rem", "$extensions": { "org.symfinity": { "step": 4 } } }
          }
        }
        JSON;

        $doc = (new DtcgJsonReader())->fromString($json);
        $flat = $doc->flatten();

        self::assertSame(['color.blue.600', 'color.primary', 'space.4'], array_keys($flat));

        // group $type inheritance
        self::assertSame(TokenType::Color, $flat['color.blue.600']->type());
        self::assertSame(TokenType::Color, $flat['color.primary']->type());
        self::assertSame(TokenType::Dimension, $flat['space.4']->type());

        // alias parsing
        self::assertTrue($flat['color.primary']->isAlias());
        self::assertInstanceOf(AliasReference::class, $flat['color.primary']->value());
        self::assertSame('color.blue.600', (string) $flat['color.primary']->value()->target());
        self::assertSame('brand', $flat['color.primary']->description());

        // structured color value preserved
        self::assertSame(['colorSpace' => 'oklch', 'components' => [0.55, 0.21, 256]], $flat['color.blue.600']->value());

        // extensions preserved
        self::assertSame(['org.symfinity' => ['step' => 4]], $flat['space.4']->extensions());
    }
}
