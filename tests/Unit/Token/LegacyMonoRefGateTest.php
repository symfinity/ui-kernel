<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

final class LegacyMonoRefGateTest extends TestCase
{
    /** @var list<string> */
    private const LEGACY_PATTERNS = [
        'mono.pure.',
        'mono.cool.',
        'mono.warm.',
        'mono.wood.',
        'mono.pope.',
        'mono.evil.',
    ];

    #[Test]
    public function builtInThemeAssetsContainNoLegacyMonoRefs(): void
    {
        $packageRoot = dirname(__DIR__, 3);
        $finder = (new Finder())
            ->files()
            ->in($packageRoot . '/config/themes')
            ->name('*.yaml');

        $violations = [];
        foreach ($finder as $file) {
            $contents = $file->getContents();
            foreach (self::LEGACY_PATTERNS as $pattern) {
                if (str_contains($contents, $pattern)) {
                    $violations[] = sprintf('%s contains %s', $file->getRelativePathname(), $pattern);
                }
            }
        }

        self::assertSame([], $violations);
    }
}
