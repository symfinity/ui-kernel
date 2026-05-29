<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Renderer;

use Symfinity\UiKernel\Component\Alert;
use Symfinity\UiKernel\Component\Button;
use Symfinity\UiKernel\Component\Card;
use Symfinity\UiKernel\Component\FormRow;
use Symfinity\UiKernel\Component\UiComponent;
use Symfinity\UiKernel\Page\UiPage;

final class HtmlRenderer
{
    public function render(UiPage $page): string
    {
        $parts = ['<main data-ui-fragment="page-root">'];
        $parts[] = sprintf('<h1 data-ui-role="heading">%s</h1>', htmlspecialchars($page->title(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));

        foreach ($page->children() as $child) {
            $parts[] = $this->renderComponent($child);
        }

        $parts[] = '</main>';

        return implode("\n", $parts);
    }

    private function renderComponent(UiComponent $component): string
    {
        return match (true) {
            $component instanceof Button => $this->renderButton($component),
            $component instanceof Card => $this->renderCard($component),
            $component instanceof Alert => $this->renderAlert($component),
            $component instanceof FormRow => $this->renderFormRow($component),
            default => $this->renderGeneric($component),
        };
    }

    private function renderButton(Button $button): string
    {
        $attrs = $this->baseAttributes($button);

        return sprintf(
            '<button type="button" %s>%s</button>',
            $attrs,
            htmlspecialchars($button->label(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        );
    }

    private function renderCard(Card $card): string
    {
        $attrs = $this->baseAttributes($card);

        return sprintf(
            '<section %s><h2>%s</h2><p>%s</p></section>',
            $attrs,
            htmlspecialchars($card->title(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($card->body(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        );
    }

    private function renderAlert(Alert $alert): string
    {
        $attrs = $this->baseAttributes($alert);

        return sprintf(
            '<div %s>%s</div>',
            $attrs,
            htmlspecialchars($alert->message(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        );
    }

    private function renderFormRow(FormRow $row): string
    {
        $attrs = $this->baseAttributes($row);
        $disabled = isset($row->state()['disabled']) ? ' disabled' : '';

        return sprintf(
            '<div %s><label>%s</label><input type="%s"%s /></div>',
            $attrs,
            htmlspecialchars($row->label(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($row->inputType(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            $disabled,
        );
    }

    private function renderGeneric(UiComponent $component): string
    {
        return sprintf('<div %s></div>', $this->baseAttributes($component));
    }

    private function baseAttributes(UiComponent $component): string
    {
        $parts = [
            sprintf('data-ui-role="%s"', htmlspecialchars($component->role(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')),
            sprintf('data-ui-variant="%s"', htmlspecialchars($component->variant(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')),
        ];

        foreach ($component->state() as $name => $value) {
            $parts[] = sprintf('data-ui-state-%s="%s"', htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        }

        $fragment = $component->fragment();
        if ($fragment !== null) {
            $parts[] = sprintf('data-ui-fragment="%s"', htmlspecialchars($fragment->id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        }

        return implode(' ', $parts);
    }
}
