<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

use Symfinity\UiKernel\Dtcg\BuiltinThemeVariant;
use Symfinity\UiKernel\Dtcg\ThemeDtcgResolver;
use Symfinity\UiKernel\Token\AuthoringThemeConfig;
use Symfinity\UiKernel\Token\DesignTokenSet;
use Symfinity\UiKernel\Token\ThemeTokenResolver;
use Symfinity\UiKernel\Token\UserTokenSet;

final readonly class DefinedTheme implements Theme
{
    public function __construct(
        private string $id,
        private string $label,
        private string $schemaVersion,
        private DesignTokenSet $tokenSet,
        private bool $scrollMotion = false,
        private ?string $designSystemId = null,
    ) {
    }

    public static function fromVariant(
        BuiltinThemeVariant $variant,
        ?ThemeDtcgResolver $resolver = null,
        ?UserTokenSet $userTokens = null,
    ): self {
        $resolver ??= new ThemeDtcgResolver(
            new \Symfinity\UiKernel\Dtcg\LayerStackBuilder(
                new \Symfinity\UiKernel\Dtcg\DesignSystemLayerRegistry(
                    \Symfinity\UiKernel\Dtcg\DesignSystemLayerRegistry::defaultDirectory(),
                ),
            ),
        );
        $tokenSet = $resolver->resolve($variant, $userTokens);

        return new self(
            $variant->id(),
            $variant->label(),
            $variant->schemaVersion(),
            $tokenSet,
            $variant->scrollMotion(),
            $variant->designSystemId(),
        );
    }

    public static function fromAuthoring(
        AuthoringThemeConfig $config,
        ThemeTokenResolver $resolver,
        ?UserTokenSet $userTokens = null,
    ): self {
        $tokenSet = $resolver->resolve($config, $userTokens);

        return new self(
            $config->id(),
            $config->label(),
            $config->schemaVersion(),
            $tokenSet,
            $config->scrollMotion(),
            null,
        );
    }

    /** @deprecated Use {@see fromAuthoring()} — bespoke consumer themes only. */
    public static function fromConfig(
        AuthoringThemeConfig $config,
        ThemeTokenResolver $resolver,
        ?UserTokenSet $userTokens = null,
    ): self {
        return self::fromAuthoring($config, $resolver, $userTokens);
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

    public function designSystemId(): ?string
    {
        return $this->designSystemId;
    }
}
