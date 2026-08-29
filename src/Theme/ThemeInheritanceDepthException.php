<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

final class ThemeInheritanceDepthException extends \InvalidArgumentException
{
    public static function exceeded(string $leafId, int $depth, int $maxDepth): self
    {
        return new self(sprintf(
            'Theme variant "%s" inheritance chain depth %d exceeds max_depth %d.',
            $leafId,
            $depth,
            $maxDepth,
        ));
    }
}
