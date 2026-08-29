<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

/** 065 W7 — lab tier CSS lives in symfinity/ux-blocks-lab. */
final class CssGeneratorLabRolesTest extends TestCase
{
    /** @return list<string> */
    private static function labSampleRoles(): array
    {
        return ['kanban-board', 'todo-list-form', 'game-2048', 'crash-recovery-modal'];
    }

    #[Test]
    public function schemaTwoOmitsLabRoleSelectors(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V2_0);

        foreach (self::labSampleRoles() as $role) {
            self::assertStringNotContainsString(
                '[data-ui-role="' . $role . '"]',
                $css,
                sprintf('Kernel must not ship lab role "%s" after 065 W7', $role),
            );
        }
    }
}
