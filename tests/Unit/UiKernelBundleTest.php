<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\UiKernelBundle;

final class UiKernelBundleTest extends TestCase
{
    #[Test]
    public function bundleSourceDoesNotDeclareConfigureRoutes(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/src/UiKernelBundle.php');

        self::assertIsString($source);
        self::assertStringNotContainsString(
            'function configureRoutes',
            $source,
            'UiKernelBundle must not register Symfony routes.',
        );
    }

    #[Test]
    public function bundleDoesNotImportRouteYaml(): void
    {
        self::assertFileDoesNotExist(
            dirname(__DIR__, 2) . '/config/routes.yaml',
            'Kernel package must not ship route YAML.',
        );
    }

    #[Test]
    public function bundleHasNoHttpControllersUnderSrc(): void
    {
        $root = dirname(__DIR__, 2) . '/src';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        $controllers = [];
        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo || !$file->isFile()) {
                continue;
            }
            if (str_ends_with($file->getFilename(), 'Controller.php')) {
                $controllers[] = $file->getPathname();
            }
        }

        self::assertSame([], $controllers, 'Kernel src must not contain HTTP controller classes.');
    }
}
