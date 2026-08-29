<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Contract;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Dependency-free Contract/ namespace gate (076 FR-010 / SC-007).
 *
 * The Contract/ tree prepares a future `ui-kernel-contracts` split, so it MUST NOT depend on
 * Symfony, Twig, or any sibling kernel infrastructure namespace.
 */
final class ContractNamespaceBoundaryTest extends TestCase
{
    /** @var list<string> forbidden `use` prefixes */
    private const FORBIDDEN_PREFIXES = [
        'Symfony\\',
        'Twig\\',
        'Symfinity\\UiKernel\\Dtcg\\',
        'Symfinity\\UiKernel\\Css\\',
        'Symfinity\\UiKernel\\Http\\',
        'Symfinity\\UiKernel\\Twig\\',
        'Symfinity\\UiKernel\\DataCollector\\',
        'Symfinity\\UiKernel\\DependencyInjection\\',
        'Symfinity\\UiKernel\\Theme\\',
        'Symfinity\\UiKernel\\Token\\',
        'Symfinity\\UiKernel\\Palette\\',
        'Symfinity\\UiKernel\\Profile\\',
        'Symfinity\\UiKernel\\Internal\\',
    ];

    #[Test]
    public function contractNamespaceHasNoFrameworkOrInfrastructureDependencies(): void
    {
        $root = \dirname(__DIR__, 3) . '/src/Contract';
        self::assertDirectoryExists($root);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
        );

        $checked = 0;
        foreach ($iterator as $file) {
            \assert($file instanceof \SplFileInfo);
            if ($file->getExtension() !== 'php') {
                continue;
            }

            ++$checked;
            $contents = file_get_contents($file->getPathname());
            self::assertIsString($contents);

            if (preg_match_all('/^use\s+([^;]+);/m', $contents, $matches) === false) {
                continue;
            }

            foreach ($matches[1] as $import) {
                $import = ltrim(trim($import), '\\');
                foreach (self::FORBIDDEN_PREFIXES as $prefix) {
                    self::assertStringStartsNotWith(
                        $prefix,
                        $import,
                        sprintf('%s imports forbidden dependency "%s".', $file->getFilename(), $import),
                    );
                }
            }
        }

        self::assertGreaterThan(0, $checked, 'expected Contract/ PHP files to scan');
    }
}
