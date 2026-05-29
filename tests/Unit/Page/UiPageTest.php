<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Page;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Component\Alert;
use Symfinity\UiKernel\Component\Button;
use Symfinity\UiKernel\Component\Card;
use Symfinity\UiKernel\Page\UiPage;

final class UiPageTest extends TestCase
{
    #[Test]
    public function itBuildsATreeWithRolesAndChildren(): void
    {
        $page = new UiPage('Gallery');
        $page
            ->add(new Button('primary', 'Go'))
            ->add(new Card('Title', 'Body'))
            ->add(new Alert('info', 'Note'));

        self::assertSame('Gallery', $page->title());
        self::assertCount(3, $page->children());
        self::assertSame('button', $page->children()[0]->role());
        self::assertSame('card', $page->children()[1]->role());
        self::assertSame('card-gallery', $page->children()[1]->fragment()?->id);
    }
}
