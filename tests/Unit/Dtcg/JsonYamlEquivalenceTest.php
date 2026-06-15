<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Dtcg;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Contract\Layer\LayerRole;
use Symfinity\UiKernel\Contract\Layer\LayerStack;
use Symfinity\UiKernel\Contract\Resolver\ResolvedGraphInterface;
use Symfinity\UiKernel\Contract\Token\AliasReference;
use Symfinity\UiKernel\Dtcg\DtcgDocument;
use Symfinity\UiKernel\Dtcg\DtcgJsonReader;
use Symfinity\UiKernel\Dtcg\DtcgYamlReader;
use Symfinity\UiKernel\Dtcg\LayeredTokenResolver;

/**
 * US3 (076 FR-008 / SC-003): JSON and YAML serializations of the same DTCG token set
 * produce identical internal models and resolved graphs.
 */
final class JsonYamlEquivalenceTest extends TestCase
{
    #[Test]
    public function jsonAndYamlProduceIdenticalDocumentAndGraph(): void
    {
        $json = (new DtcgJsonReader())->read(__DIR__ . '/parity/sample.json');
        $yaml = (new DtcgYamlReader())->read(__DIR__ . '/parity/sample.yaml');

        self::assertSame($this->snapshotDocument($json), $this->snapshotDocument($yaml));

        $resolver = new LayeredTokenResolver();
        $jsonGraph = $resolver->resolve(new LayerStack($json->asLayer('sample', LayerRole::Base)));
        $yamlGraph = $resolver->resolve(new LayerStack($yaml->asLayer('sample', LayerRole::Base)));

        self::assertSame($this->snapshotGraph($jsonGraph), $this->snapshotGraph($yamlGraph));
    }

    /**
     * @return array<string, array{type: string, alias: ?string, value: mixed}>
     */
    private function snapshotDocument(DtcgDocument $doc): array
    {
        $out = [];
        foreach ($doc->flatten() as $path => $token) {
            $value = $token->value();
            $out[$path] = [
                'type' => $token->type()->value,
                'alias' => $value instanceof AliasReference ? (string) $value->target() : null,
                'value' => $value instanceof AliasReference ? null : $value,
                'description' => $token->description(),
            ];
        }

        return $out;
    }

    /**
     * @return array<string, array{type: string, value: mixed}>
     */
    private function snapshotGraph(ResolvedGraphInterface $graph): array
    {
        $out = [];
        foreach ($graph->all() as $path => $token) {
            $out[$path] = [
                'type' => $token->type()->value,
                'value' => $token->value(),
            ];
        }

        return $out;
    }
}
