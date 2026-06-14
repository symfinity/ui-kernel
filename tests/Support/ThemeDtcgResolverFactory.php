<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Support;

use Symfinity\UiKernel\Dtcg\DesignSystemLayerRegistry;
use Symfinity\UiKernel\Dtcg\LayerStackBuilder;
use Symfinity\UiKernel\Dtcg\ThemeDtcgResolver;

final class ThemeDtcgResolverFactory
{
    public static function create(): ThemeDtcgResolver
    {
        return new ThemeDtcgResolver(
            new LayerStackBuilder(
                new DesignSystemLayerRegistry(DesignSystemLayerRegistry::defaultDirectory()),
            ),
        );
    }
}
