<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

/**
 * symfinity/ux-blocks-lab incubator roles (060 T008).
 */
final class CssGeneratorLabRolesTest extends TestCase
{
    /** @return list<string> */
    private static function labRootRoles(): array
    {
        return [
            'achievement-badge',
            'author-github-badges',
            'card-swap',
            'crash-recovery-modal',
            'daisy-mockups',
            'dino-chart',
            'echo-text',
            'error-fallback-banner',
            'fab-dock-chat',
            'fade-content',
            'flashcard-deck',
            'flight-route-map',
            'flip-clock',
            'food-vote',
            'game-2048',
            'game-minesweeper',
            'game-snake',
            'heatmap',
            'inline-edit-food',
            'invoice-creator',
            'json-viewer',
            'kanban-board',
            'leaderboard',
            'lms-quiz',
            'magnetic-card',
            'marquee',
            'matching-pairs',
            'onboarding-tour',
            'partition-bar',
            'product-grid-load-more',
            'registration-form-demo',
            'scroll-fade',
            'scroll-stack',
            'slide-deck',
            'status-indicator',
            'streak-counter',
            'system-banner',
            'terminal-swapper',
            'timeline-8star',
            'todo-list-form',
            'transport-badge',
            'turbo-mercure-spa',
            'upload-files-demo',
            'video-embed',
            'wave-player',
        ];
    }

    #[Test]
    public function schemaTwoIncludesAllLabRootRoleSelectors(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V2_0);

        foreach (self::labRootRoles() as $role) {
            self::assertStringContainsString(
                '[data-ui-role="' . $role . '"]',
                $css,
                sprintf('Missing kernel CSS for lab role "%s"', $role),
            );
        }
    }
}
