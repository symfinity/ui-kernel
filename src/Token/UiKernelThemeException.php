<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use InvalidArgumentException;

final class UiKernelThemeException extends InvalidArgumentException
{
    public function __construct(
        public readonly string $errorCode,
        string $message,
    ) {
        parent::__construct(sprintf('[%s] %s', $errorCode, $message));
    }
}
