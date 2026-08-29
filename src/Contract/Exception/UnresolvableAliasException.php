<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Contract\Exception;

use RuntimeException;
use Symfinity\UiKernel\Contract\Token\TokenPath;

/**
 * Raised when an alias target is absent from the merged token map (076 FR-011).
 */
final class UnresolvableAliasException extends RuntimeException
{
    public function __construct(
        public readonly TokenPath $offendingPath,
        public readonly TokenPath $target,
    ) {
        parent::__construct(sprintf(
            'Token "%s" references unresolvable alias "{%s}".',
            $offendingPath,
            $target,
        ));
    }
}
