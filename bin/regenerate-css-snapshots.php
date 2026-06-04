<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/vendor/autoload.php';

use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;

$generator = new CssGenerator();
$dir = dirname(__DIR__) . '/tests/fixtures/snapshots';

foreach (['semantic' => 'css-016-overlay-semantic.css', 'utility' => 'css-016-overlay-utility.css'] as $id => $file) {
    $css = $generator->forTheme(ThemeCatalog::get($id));
    file_put_contents($dir . '/' . $file, $css);
    fwrite(STDOUT, "wrote {$file}\n");
}
