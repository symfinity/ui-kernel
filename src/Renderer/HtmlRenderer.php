<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Renderer;

use Symfinity\UiKernel\Component\GenericUiComponent;
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
        if (!$component instanceof GenericUiComponent) {
            return $this->renderGeneric($component);
        }

        return match ($component->role()) {
            'button' => $this->renderButton($component),
            'card' => $this->renderCard($component),
            'alert' => $this->renderAlert($component),
            'form-row' => $this->renderFormRow($component),
            default => $this->renderGeneric($component),
        };
    }

    private function renderButton(GenericUiComponent $component): string
    {
        $attrs = $this->baseAttributes($component);

        return sprintf(
            '<button type="button" %s>%s</button>',
            $attrs,
            htmlspecialchars($component->slot('label'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        );
    }

    private function renderCard(GenericUiComponent $component): string
    {
        $attrs = $this->baseAttributes($component);

        return sprintf(
            '<section %s><h2>%s</h2><p>%s</p></section>',
            $attrs,
            htmlspecialchars($component->slot('title'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($component->slot('body'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        );
    }

    private function renderAlert(GenericUiComponent $component): string
    {
        $attrs = $this->baseAttributes($component);

        return sprintf(
            '<div %s>%s</div>',
            $attrs,
            htmlspecialchars($component->slot('message'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        );
    }

    private function renderFormRow(GenericUiComponent $component): string
    {
        $attrs = $this->baseAttributes($component);
        $disabled = isset($component->state()['disabled']) ? ' disabled' : '';

        return sprintf(
            '<div %s><label>%s</label><input type="%s"%s /></div>',
            $attrs,
            htmlspecialchars($component->slot('label'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($component->slot('inputType', 'text'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
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
