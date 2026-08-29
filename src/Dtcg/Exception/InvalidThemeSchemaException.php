<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Dtcg\Exception;

final class InvalidThemeSchemaException extends \InvalidArgumentException
{
    public static function legacySchema(string $path): self
    {
        return new self(sprintf(
            'Built-in theme file "%s" uses legacy symfinity_ui_kernel.themes schema; expected W3C DTCG root (077).',
            $path,
        ));
    }
}
