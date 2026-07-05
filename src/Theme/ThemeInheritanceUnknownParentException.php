<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

final class ThemeInheritanceUnknownParentException extends \InvalidArgumentException
{
    public static function forVariant(string $childId, string $parentId): self
    {
        return new self(sprintf(
            'Theme variant "%s" extends unknown parent "%s".',
            $childId,
            $parentId,
        ));
    }
}
