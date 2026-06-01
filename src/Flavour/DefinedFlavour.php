<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Flavour;

use Symfinity\UiKernel\Token\DesignTokenSet;
use Symfinity\UiKernel\Token\FlavourThemeConfig;
use Symfinity\UiKernel\Token\ThemeTokenResolver;
use Symfinity\UiKernel\Token\UserTokenSet;

final readonly class DefinedFlavour implements Flavour
{
    public function __construct(
        private string $id,
        private string $label,
        private string $schemaVersion,
        private DesignTokenSet $tokenSet,
        private bool $scrollMotion = false,
    ) {
    }

    public static function fromConfig(
        FlavourThemeConfig $config,
        ?ThemeTokenResolver $resolver = null,
        ?UserTokenSet $userTokens = null,
    ): self {
        $resolver ??= new ThemeTokenResolver();
        $tokenSet = $resolver->resolve($config, $userTokens);

        return new self(
            $config->id(),
            $config->label(),
            $config->schemaVersion(),
            $tokenSet,
            $config->scrollMotion(),
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function schemaVersion(): string
    {
        return $this->schemaVersion;
    }

    public function tokens(): DesignTokenSet
    {
        return $this->tokenSet;
    }

    public function scrollMotion(): bool
    {
        return $this->scrollMotion;
    }
}
