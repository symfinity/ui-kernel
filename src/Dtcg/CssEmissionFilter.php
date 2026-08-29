<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Dtcg;

use Symfinity\UiKernel\Contract\Resolver\ResolvedGraphInterface;
use Symfinity\UiKernel\Contract\Token\TokenInterface;
use Symfinity\UiKernel\Contract\Token\TokenPath;

/**
 * Filters palette primitives from a resolved graph before CSS emission (077).
 *
 * Base-layer ramps ({@code color.blue.600}, {@code color.mono.cool.500}) resolve aliases
 * but MUST NOT emit as {@code --ui-*} custom properties.
 */
final class CssEmissionFilter
{
    /**
     * @return array<string, TokenInterface>
     */
    public function emitTokens(ResolvedGraphInterface $graph): array
    {
        $filtered = [];
        foreach ($graph->all() as $path => $token) {
            if ($this->isPalettePrimitive(TokenPath::fromString($path))) {
                continue;
            }
            $filtered[$path] = $token;
        }

        return $filtered;
    }

    public function isPalettePrimitive(TokenPath|string $path): bool
    {
        $segments = (\is_string($path) ? TokenPath::fromString($path) : $path)->segments();
        if ($segments === [] || $segments[0] !== 'color') {
            return false;
        }

        if ($segments[1] === 'mono' && \count($segments) === 4 && ctype_digit($segments[3])) {
            return true;
        }

        return \count($segments) === 3 && ctype_digit($segments[2]);
    }
}
