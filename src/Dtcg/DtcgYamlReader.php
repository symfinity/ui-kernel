<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Dtcg;

use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;

/**
 * Reads DTCG YAML into the same {@see DtcgDocument} model as the JSON reader (076).
 *
 * YAML is treated as an alternative serialization of the DTCG `$value`/`$type` structure,
 * not the legacy bespoke `semantics:/preset/tone` theme schema.
 */
final class DtcgYamlReader
{
    public function __construct(
        private readonly DtcgTreeBuilder $treeBuilder = new DtcgTreeBuilder(),
    ) {
    }

    public function fromString(string $yaml): DtcgDocument
    {
        /** @var mixed $decoded */
        $decoded = Yaml::parse($yaml);
        if (!\is_array($decoded)) {
            throw new InvalidArgumentException('DTCG YAML must parse to a mapping.');
        }

        /** @var array<string, mixed> $decoded */
        return $this->treeBuilder->build($decoded);
    }

    public function read(string $path): DtcgDocument
    {
        /** @var mixed $decoded */
        $decoded = Yaml::parseFile($path);
        if (!\is_array($decoded)) {
            throw new InvalidArgumentException(sprintf('DTCG YAML file "%s" must parse to a mapping.', $path));
        }

        /** @var array<string, mixed> $decoded */
        return $this->treeBuilder->build($decoded);
    }
}
