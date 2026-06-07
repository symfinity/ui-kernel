<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\DependencyInjection;

use Symfinity\UiKernel\Profile\SystemProfileRegistry;
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

        $container->setParameter('symfinity.ui_kernel.default_theme', $config['default_theme']);
        $container->setParameter('symfinity.ui_kernel.default_variant', $config['default_variant']);
        $container->setParameter('symfinity.ui_kernel.schema_version', $config['schema_version']);
        $container->setParameter('symfinity.ui_kernel.default_lineage', $config['default_variant']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');

        $container->register(UserTokenSet::class)
            ->setAutowired(false)
            ->setAutoconfigured(false)
            ->setArgument('$tokens', $config['user_tokens']);

        $container->register(SystemProfileRegistry::class)
            ->setAutowired(false)
            ->setAutoconfigured(false)
            ->setArgument('$config', $config['system_profile']);
    }

    public function getAlias(): string
    {
        return 'symfinity_ui_kernel';
    }
}
