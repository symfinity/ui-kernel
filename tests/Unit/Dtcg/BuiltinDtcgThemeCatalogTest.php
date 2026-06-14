<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Dtcg;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Dtcg\BuiltinDtcgThemeCatalog;
use Symfinity\UiKernel\Dtcg\Exception\InvalidThemeSchemaException;

final class BuiltinDtcgThemeCatalogTest extends TestCase
{
    #[Test]
    public function catalogLoadsAllBuiltInVariants(): void
    {
        $catalog = new BuiltinDtcgThemeCatalog(BuiltinDtcgThemeCatalog::defaultDirectory());
        $ids = array_map(static fn ($v) => $v->id(), $catalog->all());

        foreach (['default', 'default-dark', 'semantic', 'semantic-dark', 'utility', 'utility-dark'] as $id) {
            self::assertContains($id, $ids, $id);
        }
    }

    #[Test]
    public function variantExposesDesignSystemIdFromMeta(): void
    {
        $variant = (new BuiltinDtcgThemeCatalog(BuiltinDtcgThemeCatalog::defaultDirectory()))->get('default');

        self::assertSame('chameleon', $variant->designSystemId());
    }

    #[Test]
    public function legacyBespokeSchemaInMetaThrows(): void
    {
        BuiltinDtcgThemeCatalog::reset();

        $dir = sys_get_temp_dir() . '/ui-kernel-dtcg-catalog-' . uniqid('', true);
        mkdir($dir . '/broken', 0755, true);
        file_put_contents($dir . '/broken/theme.meta.yaml', <<<'YAML'
symfinity_ui_kernel:
  themes:
    broken: {}
YAML);

        try {
            $this->expectException(InvalidThemeSchemaException::class);

            (new BuiltinDtcgThemeCatalog($dir))->all();
        } finally {
            BuiltinDtcgThemeCatalog::reset();
        }
    }
}
