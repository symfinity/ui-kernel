<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Renderer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Component\GenericUiComponent;
use Symfinity\UiKernel\Page\UiFragment;
use Symfinity\UiKernel\Page\UiPage;
use Symfinity\UiKernel\Renderer\HtmlRenderer;

final class HtmlRendererTest extends TestCase
{
    #[Test]
    public function itEmitsSemanticDataUiAttributes(): void
    {
        $page = new UiPage('UI Kernel theme showcase');
        $page
            ->add(new GenericUiComponent('button', 'primary', [], null, ['label' => 'Primary action']))
            ->add(new GenericUiComponent('button', 'secondary', [], null, ['label' => 'Secondary action']))
            ->add(new GenericUiComponent('button', 'danger', [], null, ['label' => 'Danger action']))
            ->add(new GenericUiComponent('button', 'success', [], null, ['label' => 'Success action']))
            ->add(new GenericUiComponent(
                'card',
                'default',
                [],
                new UiFragment('card-gallery'),
                ['title' => 'Gallery card', 'body' => 'Fixed component tree — only tokens and data-theme change between flavours.'],
            ))
            ->add(new GenericUiComponent('alert', 'danger', [], null, [
                'message' => 'Themes are Symfinity token packs inspired by common systems, not official Bootstrap or Tailwind.',
            ]))
            ->add(new GenericUiComponent('form-row', 'default', ['disabled' => 'true'], null, [
                'label' => 'Sample field',
                'inputType' => 'text',
            ]));

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
        $page->add(new GenericUiComponent('button', 'secondary', [], null, ['label' => 'X']));
        $html = (new HtmlRenderer())->render($page);

        self::assertStringContainsString('data-ui-fragment="page-root"', $html);
        self::assertStringContainsString('>X</button>', $html);
    }

    #[Test]
    public function itRendersUnknownRoleAsGenericDiv(): void
    {
        $page = new UiPage('T');
        $page->add(new GenericUiComponent('custom-widget', 'default'));
        $html = (new HtmlRenderer())->render($page);

        self::assertStringContainsString('data-ui-role="custom-widget"', $html);
        self::assertStringContainsString('<div data-ui-role="custom-widget"', $html);
    }
}
