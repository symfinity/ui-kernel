<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;

/**
 * CI guard: ux-blocks tier CSS must not embed sRGB literals (065).
 */
final class UxBlocksRoleCssTokenAuditTest extends TestCase
{
    #[Test]
    public function uxBlocksTierStylesUseKernelTokensNotRawSrgb(): void
    {
        $packagesRoot = dirname(__DIR__, 5) . '/packages';
        $violations = [];

        foreach (glob($packagesRoot . '/ux-blocks-*/assets/styles', GLOB_ONLYDIR) ?: [] as $stylesDir) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($stylesDir, \FilesystemIterator::SKIP_DOTS),
            );
            $regex = new RegexIterator($iterator, '/\.css$/i');

            /** @var SplFileInfo $file */
            foreach ($regex as $file) {
                $contents = file_get_contents($file->getPathname());
                if ($contents === false) {
                    continue;
                }

                if (preg_match('/#[0-9a-fA-F]{3,8}\b|(?:rgb|hsl)a?\(/', $contents) === 1) {
                    $violations[] = str_replace($packagesRoot . '/', '', $file->getPathname());
                }
            }
        }

        self::assertSame([], $violations, 'Raw sRGB literals in ux-blocks role CSS: ' . implode(', ', $violations));
    }
}
