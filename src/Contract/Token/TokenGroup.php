<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Contract\Token;

/**
 * A named DTCG container of tokens and subgroups, with an optional group-level `$type`
 * inherited by descendants lacking their own `$type` (076).
 */
final class TokenGroup
{
    /**
     * @param array<string, Token|TokenGroup> $children
     */
    public function __construct(
        private readonly ?TokenType $groupType,
        private readonly array $children,
        private readonly ?string $description = null,
    ) {
    }

    public function groupType(): ?TokenType
    {
        return $this->groupType;
    }

    /**
     * @return array<string, Token|TokenGroup>
     */
    public function children(): array
    {
        return $this->children;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    /**
     * Flatten the group tree into a path-string keyed token map (insertion order preserved).
     *
     * @return array<string, Token>
     */
    public function flatten(): array
    {
        $flat = [];
        foreach ($this->children as $child) {
            if ($child instanceof Token) {
                $flat[(string) $child->path()] = $child;

                continue;
            }

            foreach ($child->flatten() as $path => $token) {
                $flat[$path] = $token;
            }
        }

        return $flat;
    }
}
