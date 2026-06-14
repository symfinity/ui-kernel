<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use Symfinity\UiKernel\Contract\Resolver\ResolvedGraphInterface;
use Symfinity\UiKernel\Token\SemanticColourVocabulary;

/**
 * Read-only derivation of semantic colour variant names from a resolved DTCG graph (076/077).
 */
final class GraphVariantReader
{
    /**
     * @return list<string> semantic colour names under `color.*` in the graph
     */
    public function semanticColorVariants(ResolvedGraphInterface $graph): array
    {
        return SemanticColourVocabulary::fromGraph($graph)->all();
    }
}
