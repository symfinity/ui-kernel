<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Preview;

interface SessionThemeInjectionPort
{
    public function resolve(string $token, ?string $polarity = null): ?PreviewHostContext;

    public function revoke(string $token): void;
}
