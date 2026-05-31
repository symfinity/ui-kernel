<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Page;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Component\GenericUiComponent;
use Symfinity\UiKernel\Page\UiFragment;
use Symfinity\UiKernel\Page\UiPage;

final class UiPageTest extends TestCase
{
    #[Test]
    public function itBuildsATreeWithRolesAndChildren(): void
    {
        $page = new UiPage('Gallery');
        $page
            ->add(new GenericUiComponent('button', 'primary', [], null, ['label' => 'Go']))
            ->add(new GenericUiComponent(
                'card',
                'default',
                [],
                new UiFragment('card-gallery'),
                ['title' => 'Title', 'body' => 'Body'],
            ))
            ->add(new GenericUiComponent('alert', 'info', [], null, ['message' => 'Note']));

        self::assertSame('Gallery', $page->title());
        self::assertCount(3, $page->children());
        self::assertSame('button', $page->children()[0]->role());
        self::assertSame('card', $page->children()[1]->role());
        self::assertSame('card-gallery', $page->children()[1]->fragment()?->id);
    }
}
