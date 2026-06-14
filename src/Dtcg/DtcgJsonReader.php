<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Dtcg;

use InvalidArgumentException;

/**
 * Reads DTCG JSON into a {@see DtcgDocument} (076).
 */
final class DtcgJsonReader
{
    public function __construct(
        private readonly DtcgTreeBuilder $treeBuilder = new DtcgTreeBuilder(),
    ) {
    }

    public function fromString(string $json): DtcgDocument
    {
        /** @var mixed $decoded */
        $decoded = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
        if (!\is_array($decoded)) {
            throw new InvalidArgumentException('DTCG JSON must decode to an object.');
        }

        /** @var array<string, mixed> $decoded */
        return $this->treeBuilder->build($decoded);
    }

    public function read(string $path): DtcgDocument
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new InvalidArgumentException(sprintf('Cannot read DTCG JSON file "%s".', $path));
        }

        return $this->fromString($contents);
    }
}
