<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

use Symfinity\UiKernel\Token\DesignTokenSet;

interface Theme
{
    public function id(): string;

    public function label(): string;

    public function schemaVersion(): string;

    public function tokens(): DesignTokenSet;

    public function scrollMotion(): bool;

    /**
     * Optional design-system layer this theme binds to; null selects the platform default (076 FR-009).
     */
    public function designSystemId(): ?string;
}
