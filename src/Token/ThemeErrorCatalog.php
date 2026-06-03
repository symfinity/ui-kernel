<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

final class ThemeErrorCatalog
{
    public const UNKNOWN_TOKEN_KEY = 'UIK_UNKNOWN_TOKEN_KEY';

    public const MISSING_REQUIRED_TOKEN = 'UIK_MISSING_REQUIRED_TOKEN';

    public const INVALID_PALETTE_REF = 'UIK_INVALID_PALETTE_REF';

    public const PRESET_CYCLE = 'UIK_PRESET_CYCLE';

    public const DUPLICATE_THEME_ID = 'UIK_DUPLICATE_THEME_ID';

    public const REGISTRY_COLLISION = 'UIK_REGISTRY_COLLISION';

    public const FORBIDDEN_TOKEN_ALIAS = 'UIK_FORBIDDEN_TOKEN_ALIAS';

    public const INVALID_TOKEN_VALUE = 'UIK_INVALID_TOKEN_VALUE';

    /**
     * @return never
     */
    public static function throw(string $code, string $message): never
    {
        throw new UiKernelThemeException($code, $message);
    }
}
