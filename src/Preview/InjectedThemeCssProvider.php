<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Preview;

use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Token\AuthoringThemeConfig;
use Symfinity\UiKernel\Token\ThemeTokenResolver;

final class InjectedThemeCssProvider
{
    public function __construct(
        private readonly ThemeTokenResolver $resolver = new ThemeTokenResolver(),
        private readonly CssGenerator $cssGenerator = new CssGenerator(),
    ) {
    }

    public function cssFor(AuthoringThemeConfig $config): string
    {
        $tokens = $this->resolver->resolve($config);

        return $this->cssGenerator->forResolvedTokens(
            $config->id(),
            $tokens,
            $config->schemaVersion(),
            scrollMotion: $config->scrollMotion(),
        );
    }
}
