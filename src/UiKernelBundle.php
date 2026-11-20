<?php

declare(strict_types=1);

namespace Symfinity\UiKernel;

use Symfinity\UiKernel\DependencyInjection\Compiler\RegisterProfilerCollectorPass;
use Symfinity\UiKernel\DependencyInjection\UiKernelExtension;
use Symfony\Bundle\TwigBundle\DependencyInjection\Configurator\TwigConfigurator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class UiKernelBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // Priority 1: drop the collector before FrameworkBundle's ProfilerPass (priority 0) wires it into the profiler.
        $container->addCompilerPass(new RegisterProfilerCollectorPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new UiKernelExtension();
    }

    public function configureTwig(TwigConfigurator $configurator): void
    {
        $configurator->path($this->getPath() . '/templates', 'UiKernel');
    }
}
