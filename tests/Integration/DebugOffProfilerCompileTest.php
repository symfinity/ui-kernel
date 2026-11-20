<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Integration;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Tests\Integration\DebugOff\DebugOffProfilerKernel;
use Symfinity\UiKernel\UiKernelBundle;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The profiler collector is dropped when debug is off; the profiler must not keep a reference to it.
 */
final class DebugOffProfilerCompileTest extends TestCase
{
    #[Test]
    public function containerCompilesWithProfilerOnAndDebugOff(): void
    {
        $kernel = new DebugOffProfilerKernel('ui-kernel', [new UiKernelBundle()], [
            'symfinity_ui_kernel' => ['default_theme' => 'default'],
        ]);
        (new Filesystem())->remove($kernel->getCacheDir());

        $kernel->boot();

        self::assertTrue($kernel->getContainer()->has('profiler'));
        $kernel->shutdown();
    }
}
