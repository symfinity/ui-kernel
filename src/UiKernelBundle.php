<?php

declare(strict_types=1);

namespace Symfinity\UiKernel;

use Symfony\Bundle\TwigBundle\DependencyInjection\Configurator\TwigConfigurator;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class UiKernelBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function configureTwig(TwigConfigurator $configurator): void
    {
        $configurator->path($this->getPath() . '/templates', 'UiKernel');
    }
}
