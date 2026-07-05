<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

use Symfinity\UiKernel\Dtcg\BuiltinDtcgThemeCatalog;
use Symfinity\UiKernel\Dtcg\BuiltinThemeVariant;

/**
 * Validates and resolves multi-hop theme inheritance chains (140).
 */
final class ThemeInheritanceGraph
{
    /** @var array<string, string> child id => parent id */
    private array $extendsMap = [];

    /** @var array<string, BuiltinThemeVariant> */
    private array $variantsById = [];

    private bool $hasNestedTheme = false;

    private bool $hasInheritanceChain = false;

    /**
     * @param list<BuiltinThemeVariant> $variants
     */
    public function __construct(array $variants, private readonly int $maxDepth = 8)
    {
        foreach ($variants as $variant) {
            $this->variantsById[$variant->id()] = $variant;
        }

        foreach ($variants as $variant) {
            $extends = $variant->extends();
            if ($extends === null || $extends === '') {
                continue;
            }

            if (!isset($this->variantsById[$extends])) {
                throw ThemeInheritanceUnknownParentException::forVariant($variant->id(), $extends);
            }

            $this->extendsMap[$variant->id()] = $extends;
            $this->hasNestedTheme = true;
        }

        foreach (array_keys($this->extendsMap) as $leafId) {
            $chain = $this->resolveChain($leafId);
            if (\count($chain) >= 3) {
                $this->hasInheritanceChain = true;
            }
        }
    }

    public function hasNestedTheme(): bool
    {
        return $this->hasNestedTheme;
    }

    public function hasInheritanceChain(): bool
    {
        return $this->hasInheritanceChain;
    }

    public function get(string $id): BuiltinThemeVariant
    {
        return $this->variantsById[$id] ?? throw new \InvalidArgumentException(sprintf('Unknown theme variant "%s".', $id));
    }

    /**
     * Ordered variant ids from root parent to leaf (inclusive).
     *
     * @return list<string>
     */
    public function resolveChain(string $leafId): array
    {
        if (!isset($this->variantsById[$leafId])) {
            throw new \InvalidArgumentException(sprintf('Unknown theme variant "%s".', $leafId));
        }

        $chain = [];
        $visited = [];
        $current = $leafId;

        while (true) {
            if (isset($visited[$current])) {
                throw ThemeInheritanceCycleException::forPath([...$chain, $current]);
            }

            $visited[$current] = true;
            array_unshift($chain, $current);

            $parent = $this->extendsMap[$current] ?? null;
            if ($parent === null) {
                break;
            }

            $current = $parent;
        }

        $hops = \count($chain) - 1;
        if ($hops > $this->maxDepth) {
            throw ThemeInheritanceDepthException::exceeded($leafId, $hops, $this->maxDepth);
        }

        return $chain;
    }

    /**
     * @return list<BuiltinThemeVariant> root → leaf
     */
    public function resolveVariantChain(BuiltinThemeVariant $leaf): array
    {
        return array_map(
            fn (string $id): BuiltinThemeVariant => $this->get($id),
            $this->resolveChain($leaf->id()),
        );
    }

    public static function fromCatalog(BuiltinDtcgThemeCatalog $catalog, int $maxDepth = 8): self
    {
        return new self($catalog->all(), $maxDepth);
    }
}
