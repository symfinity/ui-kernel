<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Integration\DebugOff;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Dev-like kernel with the profiler on and `debug=false` (APP_DEBUG=0 in dev).
 */
final class DebugOffProfilerKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * @param list<BundleInterface>              $extraBundles
     * @param array<string, array<string, mixed>> $extensionConfigs
     */
    public function __construct(
        private readonly string $variant,
        private readonly array $extraBundles,
        private readonly array $extensionConfigs = [],
    ) {
        parent::__construct('dev', false);
    }

    /**
     * @return list<BundleInterface>
     */
    public function registerBundles(): array
    {
        return [new FrameworkBundle(), new TwigBundle(), new WebProfilerBundle(), ...$this->extraBundles];
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/symfinity-debug-off-' . $this->variant . '/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/symfinity-debug-off-' . $this->variant . '/log';
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'test-secret',
            'test' => true,
            'php_errors' => ['log' => false],
            'profiler' => ['enabled' => true, 'collect' => true],
        ]);
        $container->extension('web_profiler', ['toolbar' => true]);

        foreach ($this->extensionConfigs as $alias => $config) {
            $container->extension($alias, $config);
        }
    }
}
