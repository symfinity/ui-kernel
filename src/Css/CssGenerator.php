<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Css;

use Symfinity\UiKernel\Flavour\Flavour;
use Symfinity\UiKernel\Profile\SystemProfile;
use Symfinity\UiKernel\Profile\SystemProfileRegistry;
use Symfinity\UiKernel\Token\DesignTokenSet;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class CssGenerator
{
    public function __construct(
        private readonly ?SystemProfileRegistry $profileRegistry = null,
    ) {
    }

    public function forFlavour(Flavour $flavour, ?string $schemaVersion = null): string
    {
        $schemaVersion ??= $flavour->schemaVersion();

        return $this->forResolvedTokens(
            $flavour->id(),
            $flavour->tokens(),
            $schemaVersion,
            $this->resolveProfile(),
        );
    }

    public function forResolvedTokens(
        string $flavourId,
        DesignTokenSet $tokens,
        ?string $schemaVersion = null,
        ?SystemProfile $profile = null,
    ): string {
        $schemaVersion ??= $tokens->schemaVersion();
        $profile ??= $this->resolveProfile();
        $lines = [];
        $selector = sprintf('[data-theme="%s"]', $flavourId);
        $lines[] = sprintf('/* ui-kernel schema:%s profile:%s */', $schemaVersion, $profile->id);
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

        if ($schemaVersion === ThemeTokenSchema::V2_0) {
            $lines[] = $this->profileGlobals($profile);
        }

        $lines[] = $this->roleRules($schemaVersion, $profile);

        return implode("\n", $lines);
    }

    /**
     * Cache pool key fragment when Symfony cache is enabled (css-generation contract).
     *
     * @return array{flavourId: string, userTokenHash: string, schemaVersion: string, systemProfileId: string, profileHash: string}
     */
    public static function cacheKeyParts(
        string $flavourId,
        string $userTokenHash,
        string $schemaVersion,
        SystemProfile $profile,
    ): array {
        return [
            'flavourId' => $flavourId,
            'userTokenHash' => $userTokenHash,
            'schemaVersion' => $schemaVersion,
            'systemProfileId' => $profile->id,
            'profileHash' => $profile->hash(),
        ];
    }

    private function resolveProfile(): SystemProfile
    {
        return $this->profileRegistry?->resolve() ?? SystemProfile::chameleonDefault();
    }

    private function profileGlobals(SystemProfile $profile): string
    {
        $lines = [':root {'];
        foreach ($profile->zIndexLayers() as $layer => $value) {
            $lines[] = sprintf('  --ui-z-%s: %d;', $layer, $value);
        }
        $lines[] = '}';

        $lines[] = <<<'CSS'
@keyframes ui-shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
@keyframes ui-pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.55; }
}
CSS;

        return implode("\n", $lines);
    }

    private function roleRules(string $schemaVersion, SystemProfile $profile): string
    {
        $base = <<<'CSS'
[data-ui-fragment="page-root"] > [data-ui-role] {
  margin-block-end: var(--ui-space-md);
}
[data-ui-fragment="page-root"] > [data-ui-role]:last-child {
  margin-block-end: 0;
}
[data-ui-role="button"] {
  border-radius: var(--ui-radius-md);
  padding: var(--ui-space-sm) var(--ui-space-md);
  font-family: var(--ui-font-family-sans);
  font-size: var(--ui-font-size-md);
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--ui-space-xs);
}
[data-ui-role="button"][data-ui-variant="default"],
[data-ui-role="button"][data-ui-variant="primary"] {
  background: var(--ui-color-primary);
  color: var(--ui-color-surface-elevated);
  border: 1px solid var(--ui-color-primary);
}
[data-ui-role="button"][data-ui-variant="secondary"] {
  background: var(--ui-color-surface-elevated);
  color: var(--ui-color-text);
  border: 1px solid var(--ui-color-border);
}
[data-ui-role="button"][data-ui-variant="destructive"],
[data-ui-role="button"][data-ui-variant="danger"] {
  background: var(--ui-color-danger);
  color: #fff;
  border: 1px solid var(--ui-color-danger);
}
[data-ui-role="button"][data-ui-variant="success"] {
  background: var(--ui-color-success);
  color: #fff;
  border: 1px solid var(--ui-color-success);
}
[data-ui-role="button"][data-ui-variant="outline"] {
  background: transparent;
  color: var(--ui-color-text);
  border: 1px solid var(--ui-color-border);
}
[data-ui-role="button"][data-ui-variant="ghost"] {
  background: transparent;
  color: var(--ui-color-text);
  border: 1px solid transparent;
}
[data-ui-role="button"][data-ui-variant="link"] {
  background: transparent;
  color: var(--ui-color-primary);
  border: 1px solid transparent;
  text-decoration: underline;
}
[data-ui-role="button"][data-ui-state="disabled"] {
  opacity: 0.5;
  cursor: not-allowed;
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
[data-ui-role="alert"][data-ui-variant="danger"],
[data-ui-role="alert"][data-ui-variant="destructive"] {
  background: color-mix(in srgb, var(--ui-color-danger) 15%, var(--ui-color-surface));
  color: var(--ui-color-danger);
  border: 1px solid var(--ui-color-danger);
}
[data-ui-role="alert"][data-ui-variant="success"],
[data-ui-role="alert"][data-ui-variant="info"] {
  background: color-mix(in srgb, var(--ui-color-success) 15%, var(--ui-color-surface));
  color: var(--ui-color-success);
  border: 1px solid var(--ui-color-success);
}
[data-ui-role="alert"][data-ui-variant="warning"] {
  background: color-mix(in srgb, var(--ui-color-warning, #eab308) 15%, var(--ui-color-surface));
  color: var(--ui-color-warning, #ca8a04);
  border: 1px solid var(--ui-color-warning, #ca8a04);
}
[data-ui-role="form-row"],
[data-ui-role="field"] {
  display: flex;
  flex-direction: column;
  gap: var(--ui-space-xs);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="form-row"] label,
[data-ui-role="field"] label,
[data-ui-role="label"] {
  font-size: var(--ui-font-size-sm);
  color: var(--ui-color-text-muted);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="form-row"] input,
[data-ui-role="field"] input,
[data-ui-role="input"],
[data-ui-role="textarea"],
[data-ui-role="select"] {
  border: 1px solid var(--ui-color-border);
  border-radius: var(--ui-radius-sm);
  padding: var(--ui-space-sm);
  font-size: var(--ui-font-size-md);
  background: var(--ui-color-surface-elevated);
  color: var(--ui-color-text);
  font-family: var(--ui-font-family-sans);
  width: 100%;
  box-sizing: border-box;
}
[data-ui-role="input"][data-ui-state="disabled"],
[data-ui-role="textarea"][data-ui-state="disabled"],
[data-ui-role="select"][data-ui-state="disabled"] {
  opacity: 0.6;
  cursor: not-allowed;
}
[data-ui-role="input"][aria-invalid="true"],
[data-ui-role="textarea"][aria-invalid="true"] {
  border-color: var(--ui-color-danger);
}
[data-ui-role="checkbox"] {
  accent-color: var(--ui-color-primary);
  width: 1rem;
  height: 1rem;
}
[data-ui-role="radio-group"] {
  display: flex;
  flex-direction: column;
  gap: var(--ui-space-sm);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="separator"] {
  background: var(--ui-color-border);
  border: 0;
  margin: var(--ui-space-md) 0;
}
[data-ui-role="separator"][data-ui-variant="vertical"] {
  width: 1px;
  height: auto;
  min-height: 1rem;
  margin: 0 var(--ui-space-md);
}
[data-ui-role="separator"]:not([data-ui-variant="vertical"]) {
  height: 1px;
  width: 100%;
}
[data-ui-role="typography"],
[data-ui-role="typography"][data-ui-variant="p"] {
  font-family: var(--ui-font-family-sans);
  font-size: var(--ui-font-size-md);
  line-height: 1.5;
  color: var(--ui-color-text);
  margin: 0 0 var(--ui-space-sm);
}
[data-ui-role="typography"][data-ui-variant="h1"] {
  font-family: var(--ui-font-family-sans);
  font-size: var(--ui-font-size-xl, 1.5rem);
  font-weight: 700;
  line-height: 1.2;
  color: var(--ui-color-text);
  margin: 0 0 var(--ui-space-md);
}
[data-ui-role="empty"] {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: var(--ui-space-sm);
  padding: var(--ui-space-xl);
  text-align: center;
  font-family: var(--ui-font-family-sans);
  color: var(--ui-color-text-muted);
  border: 1px dashed var(--ui-color-border);
  border-radius: var(--ui-radius-lg);
}
[data-ui-role="table"] {
  width: 100%;
  border-collapse: collapse;
  font-family: var(--ui-font-family-sans);
  font-size: var(--ui-font-size-sm);
}
[data-ui-role="table"] th,
[data-ui-role="table"] td {
  border: 1px solid var(--ui-color-border);
  padding: var(--ui-space-sm) var(--ui-space-md);
  text-align: left;
}
[data-ui-role="table"] th {
  background: var(--ui-color-surface-elevated);
  font-weight: 600;
}
#ui-kernel-showcase.ui-kernel-crossfade {
  transition: opacity var(--ui-transition-duration) ease;
}
CSS;

        if ($schemaVersion === ThemeTokenSchema::V2_0) {
            $base .= <<<'CSS'

[data-ui-role="button"]:focus-visible,
[data-ui-role="input"]:focus-visible,
[data-ui-role="textarea"]:focus-visible,
[data-ui-role="select"]:focus-visible {
  outline: 0;
  box-shadow: 0 0 var(--ui-focus-ring-blur) var(--ui-focus-ring-width) color-mix(in srgb, var(--ui-color-focus) calc(var(--ui-focus-ring-opacity) * 100%), transparent);
}
CSS;
            $base .= $this->layoutRoleRules($profile);
        }

        return $base;
    }

    private function layoutRoleRules(SystemProfile $profile): string
    {
        $lines = [];
        $columns = $profile->columns;

        $lines[] = '[data-ui-role="grid"] {';
        $lines[] = '  display: grid;';
        $lines[] = '  gap: var(--ui-grid-gap);';
        $lines[] = sprintf('  grid-template-columns: repeat(%d, minmax(0, 1fr));', $columns);
        $lines[] = '}';

        for ($n = 1; $n <= $columns; ++$n) {
            $lines[] = sprintf('[data-ui-role="grid"][data-ui-columns="%d"] {', $n);
            $lines[] = sprintf('  grid-template-columns: repeat(%d, minmax(0, 1fr));', $n);
            $lines[] = '}';
        }

        $mdPx = $profile->breakpointPx('md') ?? 768;
        $belowMd = $mdPx - 1;
        $lines[] = sprintf('@media (max-width: %dpx) {', $belowMd);
        $lines[] = '  [data-ui-role="grid"]:not([data-ui-columns]) {';
        $lines[] = '    grid-template-columns: 1fr;';
        $lines[] = '  }';
        $lines[] = '  [data-ui-role="grid"]:not([data-ui-columns]) > [data-ui-role="grid-cell"] {';
        $lines[] = '    grid-column: 1 / -1;';
        $lines[] = '  }';
        $lines[] = '}';

        for ($span = 1; $span <= $columns; ++$span) {
            $lines[] = sprintf('[data-ui-role="grid-cell"][data-ui-span="%d"] {', $span);
            $lines[] = sprintf('  grid-column: span %d;', $span);
            $lines[] = '}';
        }

        foreach ($profile->breakpoints as $name => $px) {
            for ($span = 1; $span <= $columns; ++$span) {
                $lines[] = sprintf('@media (min-width: %dpx) {', $px);
                $lines[] = sprintf('  [data-ui-role="grid-cell"][data-ui-span-%s="%d"] {', $name, $span);
                $lines[] = sprintf('    grid-column: span %d;', $span);
                $lines[] = '  }';
                $lines[] = '}';
            }
        }

        foreach ($profile->containerMaxWidths as $name => $maxWidth) {
            $bp = $profile->breakpointPx($name);
            if ($bp === null) {
                continue;
            }
            $lines[] = sprintf('@media (min-width: %dpx) {', $bp);
            $lines[] = '  [data-ui-role="grid-container"] {';
            $lines[] = sprintf('    max-width: %dpx;', $maxWidth);
            $lines[] = '    margin-inline: auto;';
            $lines[] = '    width: 100%;';
            $lines[] = '  }';
            $lines[] = '}';
        }

        $lines[] = <<<'CSS'
[data-ui-role="stack"] {
  display: flex;
  flex-direction: column;
  gap: var(--ui-space-md);
}
[data-ui-role="stack"][data-ui-direction="horizontal"] {
  flex-direction: row;
}
[data-ui-role="stack"][data-ui-gap="xs"] { gap: var(--ui-space-xs); }
[data-ui-role="stack"][data-ui-gap="sm"] { gap: var(--ui-space-sm); }
[data-ui-role="stack"][data-ui-gap="md"] { gap: var(--ui-space-md); }
[data-ui-role="stack"][data-ui-gap="lg"] { gap: var(--ui-space-lg); }
[data-ui-role="stack"][data-ui-gap="xl"] { gap: var(--ui-space-xl); }
[data-ui-role="skeleton"] {
  background: linear-gradient(
    90deg,
    var(--ui-color-skeleton-base) 0%,
    var(--ui-color-skeleton-shine) 50%,
    var(--ui-color-skeleton-base) 100%
  );
  background-size: 200% 100%;
  animation: ui-shimmer var(--ui-motion-duration-skeleton) var(--ui-motion-easing-linear) infinite;
  border-radius: var(--ui-radius-sm);
}
[data-ui-role="skeleton"][data-ui-variant="text"] {
  min-height: var(--ui-line-height-normal, 1.5rem);
  width: 100%;
}
[data-ui-role="skeleton"][data-ui-variant="rect"] {
  min-height: 4rem;
  width: 100%;
}
[data-ui-role="skeleton"][data-ui-variant="circle"] {
  width: 3rem;
  height: 3rem;
  border-radius: var(--ui-radius-full);
}
@media (prefers-reduced-motion: reduce) {
  [data-ui-role="skeleton"] {
    animation: none;
    background: var(--ui-color-skeleton-base);
  }
}
CSS;

        return implode("\n", $lines);
    }
}
