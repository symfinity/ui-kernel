<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Dtcg;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Dtcg\DtcgJsonReader;
use Symfinity\UiKernel\Dtcg\DtcgYamlReader;

/** 078 — profile globals YAML/JSON twin parity (076 gate). */
final class ProfileGlobalsTokenLoadTest extends TestCase
{
    private const YAML = __DIR__ . '/../../../config/tokens/profile-globals.dtcg.yaml';
    private const JSON = __DIR__ . '/../../../config/tokens/profile-globals.dtcg.json';

    #[Test]
    public function yamlAndJsonReadersProduceIdenticalTokenMaps(): void
    {
        $yaml = (new DtcgYamlReader())->read(self::YAML)->flatten();
        $json = (new DtcgJsonReader())->read(self::JSON)->flatten();

        self::assertSame(array_keys($yaml), array_keys($json));

        foreach ($yaml as $path => $yamlToken) {
            self::assertArrayHasKey($path, $json);
            self::assertSame((string) $yamlToken->path(), (string) $json[$path]->path());
            self::assertSame($yamlToken->type(), $json[$path]->type());
            self::assertSame($yamlToken->value(), $json[$path]->value());
            self::assertSame($yamlToken->extensions(), $json[$path]->extensions());
        }
    }
}
