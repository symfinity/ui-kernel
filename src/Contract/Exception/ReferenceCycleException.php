<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Contract\Exception;

use RuntimeException;

/**
 * Raised when alias resolution detects a reference cycle (076 FR-011).
 */
final class ReferenceCycleException extends RuntimeException
{
    /**
     * @param list<string> $cycle dotted token paths forming the cycle
     */
    public function __construct(
        public readonly array $cycle,
    ) {
        parent::__construct(sprintf('Token reference cycle detected: %s.', implode(' -> ', $cycle)));
    }
}
