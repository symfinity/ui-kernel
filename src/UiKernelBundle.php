<?php

declare(strict_types=1);

namespace Symfinity\UiKernel;

use Symfony\Bundle\TwigBundle\DependencyInjection\Configurator\TwigConfigurator;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class UiKernelBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import($this->getPath() . '/config/routes.yaml');
    }

    public function configureTwig(TwigConfigurator $configurator): void
    {
        $configurator->path($this->getPath() . '/templates', 'UiKernel');
    }
}
