<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Css;

use Symfinity\UiKernel\Flavour\Flavour;
use Symfinity\UiKernel\Token\DesignTokenSet;

final class CssGenerator
{
    public function forFlavour(Flavour $flavour): string
    {
        return $this->forResolvedTokens($flavour->id(), $flavour->tokens());
    }

    public function forResolvedTokens(string $flavourId, DesignTokenSet $tokens): string
    {
        $lines = [];
        $selector = sprintf('[data-theme="%s"]', $flavourId);
        $lines[] = $selector . ' {';

        foreach ($tokens->all() as $key => $value) {
            $lines[] = sprintf('  %s: %s;', $key, $value);
        }

        $lines[] = '}';

        if ($flavourId === 'default') {
            $lines[] = ':root {';
            foreach ($tokens->all() as $key => $value) {
                $lines[] = sprintf('  %s: %s;', $key, $value);
            }
            $lines[] = '}';
        }

        $lines[] = $this->roleRules();

        return implode("\n", $lines);
    }

    private function roleRules(): string
    {
        return <<<'CSS'
[data-ui-fragment="page-root"] > [data-ui-role] {
  margin-block-end: var(--ui-space-md);
}
[data-ui-fragment="page-root"] > [data-ui-role]:last-child {
  margin-block-end: 0;
}
[data-ui-role="button"][data-ui-variant="primary"] {
  background: var(--ui-color-primary);
  color: var(--ui-color-surface-elevated);
  border: 1px solid var(--ui-color-primary);
  border-radius: var(--ui-radius-md);
  padding: var(--ui-space-sm) var(--ui-space-md);
  font-family: var(--ui-font-family-sans);
  font-size: var(--ui-font-size-md);
  cursor: pointer;
}
[data-ui-role="button"][data-ui-variant="secondary"] {
  background: var(--ui-color-surface-elevated);
  color: var(--ui-color-text);
  border: 1px solid var(--ui-color-border);
  border-radius: var(--ui-radius-md);
  padding: var(--ui-space-sm) var(--ui-space-md);
  font-family: var(--ui-font-family-sans);
  font-size: var(--ui-font-size-md);
  cursor: pointer;
}
[data-ui-role="button"][data-ui-variant="danger"] {
  background: var(--ui-color-danger);
  color: #fff;
  border: 1px solid var(--ui-color-danger);
  border-radius: var(--ui-radius-md);
  padding: var(--ui-space-sm) var(--ui-space-md);
  font-family: var(--ui-font-family-sans);
  font-size: var(--ui-font-size-md);
  cursor: pointer;
}
[data-ui-role="button"][data-ui-variant="success"] {
  background: var(--ui-color-success);
  color: #fff;
  border: 1px solid var(--ui-color-success);
  border-radius: var(--ui-radius-md);
  padding: var(--ui-space-sm) var(--ui-space-md);
  font-family: var(--ui-font-family-sans);
  font-size: var(--ui-font-size-md);
  cursor: pointer;
}
[data-ui-role="card"] {
  background: var(--ui-color-surface-elevated);
  color: var(--ui-color-text);
  border: 1px solid var(--ui-color-border);
  border-radius: var(--ui-radius-lg);
  padding: var(--ui-space-lg);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="alert"] {
  border-radius: var(--ui-radius-md);
  padding: var(--ui-space-md);
  font-family: var(--ui-font-family-sans);
  font-size: var(--ui-font-size-sm);
}
[data-ui-role="alert"][data-ui-variant="danger"] {
  background: color-mix(in srgb, var(--ui-color-danger) 15%, var(--ui-color-surface));
  color: var(--ui-color-danger);
  border: 1px solid var(--ui-color-danger);
}
[data-ui-role="form-row"] {
  display: flex;
  flex-direction: column;
  gap: var(--ui-space-xs);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="form-row"] label {
  font-size: var(--ui-font-size-sm);
  color: var(--ui-color-text-muted);
}
[data-ui-role="form-row"] input {
  border: 1px solid var(--ui-color-border);
  border-radius: var(--ui-radius-sm);
  padding: var(--ui-space-sm);
  font-size: var(--ui-font-size-md);
  background: var(--ui-color-surface-elevated);
  color: var(--ui-color-text);
}
#ui-kernel-showcase.ui-kernel-crossfade {
  transition: opacity var(--ui-transition-duration) ease;
}
CSS;
    }
}
