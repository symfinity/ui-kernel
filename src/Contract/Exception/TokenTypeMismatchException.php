<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Contract\Exception;

use RuntimeException;
use Symfinity\UiKernel\Contract\Token\TokenPath;
use Symfinity\UiKernel\Contract\Token\TokenType;

/**
 * Raised when an alias resolves to a token of an incompatible `$type` (076 FR-011).
 */
final class TokenTypeMismatchException extends RuntimeException
{
    public function __construct(
        public readonly TokenPath $offendingPath,
        public readonly TokenType $expected,
        public readonly TokenType $actual,
    ) {
        parent::__construct(sprintf(
            'Token "%s" of type "%s" resolves to incompatible type "%s".',
            $offendingPath,
            $expected->value,
            $actual->value,
        ));
    }
}
