<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Dtcg;

use Symfinity\UiKernel\Contract\Token\AliasReference;
use Symfinity\UiKernel\Contract\Token\Token;
use Symfinity\UiKernel\Contract\Token\TokenGroup;
use Symfinity\UiKernel\Contract\Token\TokenPath;
use Symfinity\UiKernel\Contract\Token\TokenType;

/**
 * Builds a {@see DtcgDocument} from a decoded DTCG array tree (shared by JSON and YAML readers).
 *
 * Applies group `$type` inheritance and parses `{alias}` values.
 */
final class DtcgTreeBuilder
{
    /**
     * @param array<string, mixed> $tree decoded DTCG document (JSON or YAML)
     */
    public function build(array $tree): DtcgDocument
    {
        /** @var array<string, mixed> $extensions */
        $extensions = isset($tree['$extensions']) && \is_array($tree['$extensions'])
            ? $tree['$extensions']
            : [];

        $root = $this->buildGroup($tree, null, null);

        return new DtcgDocument($root, $extensions);
    }

    /**
     * @param array<string, mixed> $node
     */
    private function buildGroup(array $node, ?TokenType $inheritedType, ?TokenPath $prefix): TokenGroup
    {
        $groupType = isset($node['$type']) && \is_string($node['$type'])
            ? TokenType::fromDtcg($node['$type'])
            : $inheritedType;

        $description = isset($node['$description']) && \is_string($node['$description'])
            ? $node['$description']
            : null;

        $children = [];
        foreach ($node as $name => $child) {
            if (\is_string($name) && str_starts_with($name, '$')) {
                continue;
            }
            if (!\is_array($child)) {
                continue;
            }

            $name = (string) $name;
            $path = $prefix === null ? TokenPath::fromString($name) : $prefix->child($name);

            if (\array_key_exists('$value', $child)) {
                $children[$name] = $this->buildToken($child, $groupType, $path);

                continue;
            }

            $children[$name] = $this->buildGroup($child, $groupType, $path);
        }

        return new TokenGroup($groupType, $children, $description);
    }

    /**
     * @param array<string, mixed> $node
     */
    private function buildToken(array $node, ?TokenType $inheritedType, TokenPath $path): Token
    {
        $type = isset($node['$type']) && \is_string($node['$type'])
            ? TokenType::fromDtcg($node['$type'])
            : ($inheritedType ?? TokenType::Unknown);

        $rawValue = $node['$value'];
        $value = AliasReference::isAlias($rawValue)
            ? AliasReference::parse($rawValue)
            : $rawValue;

        $description = isset($node['$description']) && \is_string($node['$description'])
            ? $node['$description']
            : null;

        /** @var array<string, mixed> $extensions */
        $extensions = isset($node['$extensions']) && \is_array($node['$extensions'])
            ? $node['$extensions']
            : [];

        return new Token($path, $type, $value, $description, $extensions);
    }
}
