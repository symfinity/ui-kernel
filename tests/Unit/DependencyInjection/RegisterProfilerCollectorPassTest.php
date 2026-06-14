<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\DataCollector\UiKernelDataCollector;
use Symfinity\UiKernel\DependencyInjection\Compiler\RegisterProfilerCollectorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RegisterProfilerCollectorPassTest extends TestCase
{
    #[Test]
    public function removesCollectorWhenWebProfilerBundleIsNotLoaded(): void
    {
        if (class_exists(\Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class)) {
            self::markTestSkipped('WebProfilerBundle is installed in this runtime.');
        }

        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);
        $container->register(UiKernelDataCollector::class);

        (new RegisterProfilerCollectorPass())->process($container);

        self::assertFalse($container->hasDefinition(UiKernelDataCollector::class));
    }

    #[Test]
    public function keepsCollectorWhenDebugAndWebProfilerBundleAreAvailable(): void
    {
        if (!class_exists(\Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class)) {
            self::markTestSkipped('WebProfilerBundle is not installed in this runtime.');
        }

        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);
        $container->register(UiKernelDataCollector::class);

        (new RegisterProfilerCollectorPass())->process($container);

        self::assertTrue($container->hasDefinition(UiKernelDataCollector::class));
    }

    #[Test]
    public function removesCollectorWhenKernelDebugIsFalse(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', false);
        $container->register(UiKernelDataCollector::class);

        (new RegisterProfilerCollectorPass())->process($container);

        self::assertFalse($container->hasDefinition(UiKernelDataCollector::class));
    }
}
