<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Dtcg;

use Symfinity\UiKernel\Contract\Layer\LayerRole;
use Symfinity\UiKernel\Contract\Layer\TokenLayer;
use Symfinity\UiKernel\Contract\Token\Token;
use Symfinity\UiKernel\Contract\Token\TokenGroup;

/**
 * A parsed DTCG token tree produced identically by the JSON and YAML readers (076).
 */
final class DtcgDocument
{
    /**
     * @param array<string, mixed> $extensions document-level DTCG `$extensions`
     */
    public function __construct(
        private readonly TokenGroup $root,
        private readonly array $extensions = [],
    ) {
    }

    public function root(): TokenGroup
    {
        return $this->root;
    }

    /**
     * @return array<string, mixed>
     */
    public function extensions(): array
    {
        return $this->extensions;
    }

    /**
     * Flatten to a path-string keyed token map.
     *
     * @return array<string, Token>
     */
    public function flatten(): array
    {
        return $this->root->flatten();
    }

    public function asLayer(string $id, LayerRole $role): TokenLayer
    {
        return new TokenLayer($id, $role, $this->flatten());
    }
}
