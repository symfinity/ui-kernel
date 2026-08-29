<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

final class ThemeInheritanceCycleException extends \InvalidArgumentException
{
    /**
     * @param list<string> $cyclePath variant ids including repeated node at end
     */
    public static function forPath(array $cyclePath): self
    {
        return new self(sprintf(
            'Theme inheritance cycle detected: %s.',
            implode(' → ', $cyclePath),
        ));
    }
}
