<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Contract\Resolver;

use OutOfBoundsException;
use Symfinity\UiKernel\Contract\Token\TokenInterface;
use Symfinity\UiKernel\Contract\Token\TokenPath;

/**
 * Immutable resolved token graph (076).
 */
final class ResolvedGraph implements ResolvedGraphInterface
{
    /**
     * @param array<string, TokenInterface> $tokens concrete tokens keyed by dotted path string
     */
    public function __construct(
        private readonly array $tokens,
        private readonly string $layerSignature,
    ) {
    }

    public function get(TokenPath|string $path): TokenInterface
    {
        $key = (string) $path;
        if (!isset($this->tokens[$key])) {
            throw new OutOfBoundsException(sprintf('No resolved token at "%s".', $key));
        }

        return $this->tokens[$key];
    }

    public function has(TokenPath|string $path): bool
    {
        return isset($this->tokens[(string) $path]);
    }

    /**
     * @return array<string, TokenInterface>
     */
    public function all(): array
    {
        return $this->tokens;
    }

    /**
     * @return list<string>
     */
    public function semanticColors(): array
    {
        $names = [];
        foreach ($this->tokens as $token) {
            $segments = $token->path()->segments();
            if (\count($segments) === 2 && $segments[0] === 'color') {
                $names[$segments[1]] = true;
            }
        }

        return array_keys($names);
    }

    public function layerSignature(): string
    {
        return $this->layerSignature;
    }
}
