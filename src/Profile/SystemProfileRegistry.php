<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Profile;

final class SystemProfileRegistry
{
    private ?SystemProfile $resolved = null;

    /**
     * @param array{
     *     id?: string,
     *     columns?: int,
     *     breakpoints?: array<string, int>,
     *     container_max_widths?: array<string, int>
     * } $config
     */
    public function __construct(
        private readonly array $config = [],
    ) {
    }

    public function resolve(): SystemProfile
    {
        return $this->resolved ??= SystemProfile::fromConfig($this->config);
    }
}
