<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Contract\Emitter;

use Symfinity\UiKernel\Contract\Resolver\ResolvedGraphInterface;

/**
 * Emits global at-rules (e.g. {@code @keyframes}) from DTCG tokens (078).
 */
interface AtRulesContributorInterface
{
    public function emitZIndexVars(ResolvedGraphInterface $graph): string;

    public function contribute(ResolvedGraphInterface $graph): string;
}
