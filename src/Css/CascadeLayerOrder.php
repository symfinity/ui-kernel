<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Css;

/**
 * Normative Chameleon platform cascade layer stack (135).
 */
final class CascadeLayerOrder
{
    public const KERNEL_TOKENS = 'kernel.tokens';

    public const KERNEL_PROFILE = 'kernel.profile';

    public const BLOCKS_CORE = 'blocks.core';

    public const BLOCKS_EXTENDED = 'blocks.extended';

    public const BLOCKS_FORM = 'blocks.form';

    public const BLOCKS_INTERACTIVE = 'blocks.interactive';

    public const BLOCKS_LIVE = 'blocks.live';

    public const BLOCKS_MARKETING = 'blocks.marketing';

    public const BLOCKS_ECOMMERCE = 'blocks.ecommerce';

    public const BLOCKS_LAB = 'blocks.lab';

    public const APP = 'app';

    /** @var list<string> */
    public const PLATFORM_LAYERS = [
        self::KERNEL_TOKENS,
        self::KERNEL_PROFILE,
        self::BLOCKS_CORE,
        self::BLOCKS_EXTENDED,
        self::BLOCKS_FORM,
        self::BLOCKS_INTERACTIVE,
        self::BLOCKS_LIVE,
        self::BLOCKS_MARKETING,
        self::BLOCKS_ECOMMERCE,
        self::BLOCKS_LAB,
        self::APP,
    ];

    public static function declaration(): string
    {
        return '@layer '.implode(",\n  ", self::PLATFORM_LAYERS).';';
    }
}
