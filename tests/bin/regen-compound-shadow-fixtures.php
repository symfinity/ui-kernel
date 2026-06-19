<?php

declare(strict_types=1);

/**
 * Regenerate CSS snapshot fixtures after compound elevation (098).
 *
 * Run from symfinity monorepo root:
 *   make php ARGS='packages/ui-kernel/tests/bin/regen-compound-shadow-fixtures.php'
 */

use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;

require dirname(__DIR__, 4) . '/vendor/autoload.php';

$generator = new CssGenerator();

$snapshotDir = dirname(__DIR__) . '/fixtures/snapshots';
foreach ([
    'css-016-overlay-semantic.css' => 'semantic',
    'css-016-overlay-utility.css' => 'utility',
    'css-016-overlay-balanced.css' => 'default',
] as $filename => $themeId) {
    file_put_contents(
        $snapshotDir . '/' . $filename,
        $generator->forTheme(ThemeCatalog::get($themeId)),
    );
    fwrite(STDOUT, "updated {$filename}\n");
}

$parityDir = dirname(__DIR__) . '/Integration/parity';
if (!is_dir($parityDir) && !mkdir($parityDir, 0o775, true) && !is_dir($parityDir)) {
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
        $parityDir . '/' . $theme->id() . '.json',
        json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n",
    );

    fwrite(STDOUT, sprintf("captured %s (%d vars)\n", $theme->id(), count($map)));
}
