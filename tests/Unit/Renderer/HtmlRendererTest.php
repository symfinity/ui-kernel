<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Renderer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Component\Button;
use Symfinity\UiKernel\Component\FormRow;
use Symfinity\UiKernel\Page\UiPage;
use Symfinity\UiKernel\Renderer\HtmlRenderer;
use Symfinity\UiKernel\Showcase\ShowcasePageFactory;

final class HtmlRendererTest extends TestCase
{
    #[Test]
    public function itEmitsSemanticDataUiAttributes(): void
    {
        $page = (new ShowcasePageFactory())->create();
        $html = (new HtmlRenderer())->render($page);

        self::assertStringContainsString('data-ui-role="button"', $html);
        self::assertStringContainsString('data-ui-variant="primary"', $html);
        self::assertStringContainsString('data-ui-variant="danger"', $html);
        self::assertStringContainsString('data-ui-variant="success"', $html);
        self::assertStringContainsString('data-ui-role="card"', $html);
        self::assertStringContainsString('data-ui-fragment="card-gallery"', $html);
        self::assertStringContainsString('data-ui-role="form-row"', $html);
        self::assertStringContainsString('data-ui-state-disabled="true"', $html);
        self::assertStringNotContainsString('btn-primary', $html);
    }

    #[Test]
    public function itRendersMinimalPage(): void
    {
        $page = new UiPage('T');
        $page->add(new Button('secondary', 'X'));
        $html = (new HtmlRenderer())->render($page);

        self::assertStringContainsString('data-ui-fragment="page-root"', $html);
        self::assertStringContainsString('>X</button>', $html);
    }
}
