<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\DataCollector\UiKernelDataCollector;
use Symfinity\UiKernel\DependencyInjection\Compiler\RegisterProfilerCollectorPass;
use Symfinity\UiKernel\DependencyInjection\UiKernelExtension;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class RegisterProfilerCollectorPassTest extends TestCase
{
    #[Test]
    public function collectorRemovedWhenKernelDebugIsFalse(): void
    {
        $container = $this->buildContainer(kernelDebug: false);

        self::assertFalse($container->hasDefinition(UiKernelDataCollector::class));
    }

    #[Test]
    public function collectorRemovedWhenWebProfilerBundleIsUnavailable(): void
    {
        if (class_exists(WebProfilerBundle::class)) {
            self::markTestSkipped('WebProfilerBundle is installed in this test runtime.');
        }

        $container = $this->buildContainer(kernelDebug: true);

        self::assertFalse($container->hasDefinition(UiKernelDataCollector::class));
    }

    #[Test]
    public function collectorRemainsWhenDebugAndWebProfilerBundleExist(): void
    {
        if (!class_exists(WebProfilerBundle::class)) {
            self::markTestSkipped('WebProfilerBundle is not installed in this test runtime.');
        }

        $container = $this->buildContainer(kernelDebug: true);

        self::assertTrue($container->hasDefinition(UiKernelDataCollector::class));
    }

    private function buildContainer(bool $kernelDebug): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', $kernelDebug);
        $container->setParameter('symfinity.ui_kernel.default_theme', 'default');
        $container->setParameter('symfinity.ui_kernel.default_variant', 'semantic');
        $container->setParameter('symfinity.ui_kernel.schema_version', '1.0');
        $container->setParameter('symfinity.ui_kernel.default_lineage', 'semantic');

        $extension = new UiKernelExtension();
        $extension->load([[
            'default_theme' => 'default',
            'default_variant' => 'semantic',
            'schema_version' => '1.0',
            'user_tokens' => [],
            'system_profile' => [],
        ]], $container);

        if (!$container->hasDefinition(UiKernelDataCollector::class)) {
            $container->setDefinition(UiKernelDataCollector::class, new Definition(UiKernelDataCollector::class));
        }

        (new RegisterProfilerCollectorPass())->process($container);

        return $container;
    }
}
