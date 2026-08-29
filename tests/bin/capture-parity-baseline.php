<?php

declare(strict_types=1);

/**
 * One-off: capture the current CssGenerator `--ui-*` output per built-in theme as the
 * parity baseline oracle for feature 076 (US1 / SC-001).
 *
 * Run from the package root:
 *   ../../sbin/php packages/ui-kernel/tests/bin/capture-parity-baseline.php  (adjust paths)
 */

use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$generator = new CssGenerator();
$outDir = dirname(__DIR__) . '/Integration/parity';
if (!is_dir($outDir) && !mkdir($outDir, 0o775, true) && !is_dir($outDir)) {
    throw new RuntimeException('Cannot create parity baseline dir.');
}

foreach (ThemeCatalog::all() as $theme) {
    $css = $generator->forTheme($theme);
    preg_match_all('/(--ui-[\w-]+):\s*([^;]+);/', $css, $matches, PREG_SET_ORDER);

    $map = [];
    foreach ($matches as $match) {
        $map[$match[1]] = trim($match[2]);
    }

    file_put_contents(
        $outDir . '/' . $theme->id() . '.json',
        json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n",
    );

    fwrite(STDOUT, sprintf("captured %s (%d vars)\n", $theme->id(), count($map)));
}
