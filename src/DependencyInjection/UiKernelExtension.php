<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\DependencyInjection;

use Symfinity\UiKernel\Token\UserTokenSet;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class UiKernelExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');

        $container->setParameter('symfinity.ui_kernel.default_flavour', $config['default_flavour']);
        $container->setParameter('symfinity.ui_kernel.schema_version', $config['schema_version']);

        $container->register(UserTokenSet::class)
            ->setAutowired(false)
            ->setAutoconfigured(false)
            ->setArgument('$tokens', $config['user_tokens']);
    }

    public function getAlias(): string
    {
        return 'symfinity_ui_kernel';
    }
}
