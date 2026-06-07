<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

final class ThemePreference
{
    public function __construct(
        public readonly string $lineage,
        public readonly ThemeColorScheme $scheme,
    ) {
    }

    public static function defaults(string $defaultLineage): self
    {
        return new self($defaultLineage, ThemeColorScheme::Auto);
    }

    public function withLineage(string $lineage): self
    {
        return new self($lineage, $this->scheme);
    }

    public function withScheme(ThemeColorScheme $scheme): self
    {
        return new self($this->lineage, $scheme);
    }
}
