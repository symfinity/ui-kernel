<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\DependencyInjection;

use Symfinity\UiKernel\Internal\TypeGuard;
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

        $container->setParameter('symfinity.ui_kernel.default_theme', TypeGuard::string($config['default_theme'] ?? null));
        $container->setParameter('symfinity.ui_kernel.default_variant', TypeGuard::string($config['default_variant'] ?? null));
        $container->setParameter('symfinity.ui_kernel.schema_version', TypeGuard::string($config['schema_version'] ?? null));
        $container->setParameter('symfinity.ui_kernel.default_lineage', TypeGuard::string($config['default_variant'] ?? null));

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

        $profileGlobalsFile = TypeGuard::string($config['dtcg']['profile_globals_layer'] ?? 'profile-globals.dtcg.yaml');
        $container->setParameter(
            'symfinity.ui_kernel.profile_globals_layer_path',
            dirname(__DIR__, 2) . '/config/tokens/' . $profileGlobalsFile,
        );
    }

    public function getAlias(): string
    {
        return 'symfinity_ui_kernel';
    }
}
