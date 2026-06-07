<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Css;

use Symfinity\UiKernel\Theme\Theme;
use Symfinity\UiKernel\Profile\SystemProfile;
use Symfinity\UiKernel\Profile\SystemProfileRegistry;
use Symfinity\UiKernel\Token\ButtonStateDerivation;
use Symfinity\UiKernel\Token\ButtonVariantMap;
use Symfinity\UiKernel\Token\DesignTokenSet;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class CssGenerator
{
    public function __construct(
        private readonly ?SystemProfileRegistry $profileRegistry = null,
    ) {
    }

    public function forTheme(Theme $theme, ?string $schemaVersion = null): string
    {
        $schemaVersion ??= $theme->schemaVersion();

        return $this->forResolvedTokens(
            $theme->id(),
            $theme->tokens(),
            $schemaVersion,
            $this->resolveProfile(),
            $theme->scrollMotion(),
        );
    }

    public function forResolvedTokens(
        string $themeId,
        DesignTokenSet $tokens,
        ?string $schemaVersion = null,
        ?SystemProfile $profile = null,
        bool $scrollMotion = false,
    ): string {
        $schemaVersion ??= $tokens->schemaVersion();
        $profile ??= $this->resolveProfile();
        $lines = [];
        $selector = sprintf('[data-theme="%s"]', $themeId);
        $lines[] = sprintf('/* ui-kernel schema:%s profile:%s */', $schemaVersion, $profile->id);
        $lines[] = $selector . ' {';

        foreach ($tokens->all() as $key => $value) {
            $lines[] = sprintf('  %s: %s;', $key, $value);
        }

        $lines[] = '}';

        if ($themeId === 'default') {
            $lines[] = ':root {';
            foreach ($tokens->all() as $key => $value) {
                $lines[] = sprintf('  %s: %s;', $key, $value);
            }
            $lines[] = '}';
        }

        if ($schemaVersion === ThemeTokenSchema::V2_0) {
            $lines[] = $this->profileGlobals($profile);
        }

        $lines[] = $this->roleRules($schemaVersion, $profile, $scrollMotion);

        return implode("\n", $lines);
    }

    /**
     * Light/dark token swap via prefers-color-scheme only — no JS. Role rules emitted once (light profile).
     */
    public function forAdaptiveThemePair(Theme $light, Theme $dark): string
    {
        if ($light->schemaVersion() !== $dark->schemaVersion()) {
            throw new \InvalidArgumentException(sprintf(
                'Adaptive pair schema mismatch: %s vs %s',
                $light->schemaVersion(),
                $dark->schemaVersion(),
            ));
        }

        $schemaVersion = $light->schemaVersion();
        $profile = $this->resolveProfile();
        $anchorId = $light->id();
        $selector = sprintf('html[data-theme="%s"]', $anchorId);
        $lines = [];
        $lines[] = sprintf('/* ui-kernel adaptive:%s+%s schema:%s profile:%s */', $anchorId, $dark->id(), $schemaVersion, $profile->id);
        $lines[] = $selector . ' {';
        $lines[] = '  color-scheme: light dark;';

        foreach ($light->tokens()->all() as $key => $value) {
            $lines[] = sprintf('  %s: %s;', $key, $value);
        }

        $lines[] = '}';

        if ($anchorId === 'default') {
            $lines[] = ':root {';
            foreach ($light->tokens()->all() as $key => $value) {
                $lines[] = sprintf('  %s: %s;', $key, $value);
            }
            $lines[] = '}';
        }

        $lines[] = '@media (prefers-color-scheme: dark) {';
        $lines[] = '  ' . $selector . ' {';

        foreach ($dark->tokens()->all() as $key => $value) {
            $lines[] = sprintf('    %s: %s;', $key, $value);
        }

        $lines[] = '  }';

        if ($anchorId === 'default') {
            $lines[] = '  :root {';
            foreach ($dark->tokens()->all() as $key => $value) {
                $lines[] = sprintf('    %s: %s;', $key, $value);
            }
            $lines[] = '  }';
        }

        $lines[] = '}';

        if ($schemaVersion === ThemeTokenSchema::V2_0) {
            $lines[] = $this->profileGlobals($profile);
        }

        $lines[] = $this->roleRules($schemaVersion, $profile, $light->scrollMotion());

        return implode("\n", $lines);
    }

    /**
     * Cache pool key fragment when Symfony cache is enabled (css-generation contract).
     *
     * @return array{
     *     themeId: string,
     *     userTokenHash: string,
     *     schemaVersion: string,
     *     presetHash: string,
     *     roleRulesVersion: string,
     *     systemProfileId: string,
     *     profileHash: string
     * }
     */
    public static function cacheKeyParts(
        string $themeId,
        string $userTokenHash,
        string $schemaVersion,
        SystemProfile $profile,
        string $presetHash = '',
    ): array {
        return CssCacheKeyPolicy::parts(
            $themeId,
            $userTokenHash,
            $schemaVersion,
            $presetHash,
            $profile,
        );
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
@keyframes ui-fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}
CSS;

        return implode("\n", $lines);
    }

    private function roleRules(string $schemaVersion, SystemProfile $profile, bool $scrollMotion = false): string
    {
        $base = <<<'CSS'
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
  color: #fff;
  border: 1px solid var(--ui-color-primary);
}
[data-ui-role="button"][data-ui-variant="secondary"] {
  background: var(--ui-color-secondary);
  color: #fff;
  border: 1px solid var(--ui-color-secondary);
}
[data-ui-role="button"][data-ui-variant="tertiary"] {
  background: var(--ui-color-tertiary);
  color: #fff;
  border: 1px solid var(--ui-color-tertiary);
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
[data-ui-role="button"][data-ui-variant="info"] {
  background: var(--ui-color-info);
  color: #fff;
  border: 1px solid var(--ui-color-info);
}
[data-ui-role="button"][data-ui-variant="warning"] {
  background: var(--ui-color-warning);
  color: #fff;
  border: 1px solid var(--ui-color-warning);
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
[data-ui-role="button"][data-ui-size="sm"] {
  padding: var(--ui-space-xs) var(--ui-space-sm);
  font-size: var(--ui-font-size-sm);
  line-height: var(--ui-line-height-tight);
}
[data-ui-role="button"][data-ui-size="default"] {
  padding: var(--ui-space-sm) var(--ui-space-md);
  font-size: var(--ui-font-size-md);
}
[data-ui-role="button"][data-ui-size="lg"] {
  padding: var(--ui-space-md) var(--ui-space-lg);
  font-size: var(--ui-font-size-lg, var(--ui-font-size-md));
  line-height: var(--ui-line-height-normal);
}
[data-ui-role="button"][data-ui-layout="block"] {
  display: flex;
  width: 100%;
  box-sizing: border-box;
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
[data-ui-role="alert"][data-ui-variant="success"] {
  background: color-mix(in srgb, var(--ui-color-success) 15%, var(--ui-color-surface));
  color: var(--ui-color-success);
  border: 1px solid var(--ui-color-success);
}
[data-ui-role="alert"][data-ui-variant="info"] {
  background: color-mix(in srgb, var(--ui-color-info) 15%, var(--ui-color-surface));
  color: var(--ui-color-info);
  border: 1px solid var(--ui-color-info);
}
[data-ui-role="alert"][data-ui-variant="warning"] {
  background: color-mix(in srgb, var(--ui-color-warning, #eab308) 15%, var(--ui-color-surface));
  color: var(--ui-color-warning, #ca8a04);
  border: 1px solid var(--ui-color-warning, #ca8a04);
}
CSS;
        $base .= $this->alertActionButtonRules();
        $base .= <<<'CSS'
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
  transition: opacity var(--ui-motion-duration-normal) ease;
}
[data-ui-role="accordion"] summary {
  cursor: pointer;
}
[data-ui-role="accordion"] summary * {
  cursor: inherit;
}
CSS;

        $base .= $this->imageRoleRules();
        $base .= $this->authLayoutRoleRules();
        $base .= $this->avatarRoleRules();
        $base .= $this->badgeRoleRules();
        $base .= $this->breadcrumbRoleRules();

        if ($schemaVersion === ThemeTokenSchema::V2_0) {
            $base .= $this->buttonInteractionRules();
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
            $base .= $this->v1CoreRoleRules();
            $base .= $this->nativeOverlayRules();
            $base .= $this->anchorMenuRules();
            $base .= $this->extendedRoleRules();
            $base .= $this->marketingRoleRules();
            $base .= $this->extendedOverlayPanelRules();
            $base .= $this->scrollAndLoadingRules($scrollMotion);
        }

        return $base;
    }

    private function buttonInteractionRules(): string
    {
        $guard = ButtonStateDerivation::interactionGuard();
        $lines = [];

        foreach (ButtonVariantMap::cssVariantSelectors() as $semanticVariant => $attributeValues) {
            $tokenVar = ButtonVariantMap::semanticTokenKey($semanticVariant);
            $hoverBg = ButtonStateDerivation::cssHoverBackground($tokenVar);
            $activeBg = ButtonStateDerivation::cssActiveBackground($tokenVar);

            foreach ($attributeValues as $attributeValue) {
                $selector = '[data-ui-role="button"][data-ui-variant="' . $attributeValue . '"]';
                $lines[] = $selector . ':hover' . $guard . ' {';
                $lines[] = '  background: ' . $hoverBg . ';';
                $lines[] = '  border-color: ' . $hoverBg . ';';
                $lines[] = '}';
                $lines[] = $selector . ':active' . $guard . ' {';
                $lines[] = '  background: ' . $activeBg . ';';
                $lines[] = '  border-color: ' . $activeBg . ';';
                $lines[] = '}';
            }
        }

        $lines[] = '[data-ui-role="button"][disabled],';
        $lines[] = '[data-ui-role="button"][aria-disabled="true"],';
        $lines[] = '[data-ui-role="button"][data-ui-state="disabled"],';
        $lines[] = '[data-ui-role="button"][data-ui-state="loading"] {';
        $lines[] = '  cursor: not-allowed;';
        $lines[] = '  pointer-events: none;';
        $lines[] = '}';

        return implode("\n", $lines) . "\n";
    }

    private function alertActionButtonRules(): string
    {
        return <<<'CSS'
[data-ui-role="alert"] [data-ui-role="button"] {
  margin-block-start: var(--ui-space-sm);
  font-weight: var(--ui-font-weight-medium, 500);
}
[data-ui-role="alert"][data-ui-variant="info"] [data-ui-role="button"] {
  background: color-mix(in srgb, var(--ui-color-info) 28%, var(--ui-color-surface-elevated));
  color: var(--ui-color-info);
  border: 1px solid var(--ui-color-info);
}
[data-ui-role="alert"][data-ui-variant="success"] [data-ui-role="button"] {
  background: color-mix(in srgb, var(--ui-color-success) 28%, var(--ui-color-surface-elevated));
  color: var(--ui-color-success);
  border: 1px solid var(--ui-color-success);
}
[data-ui-role="alert"][data-ui-variant="warning"] [data-ui-role="button"] {
  background: color-mix(in srgb, var(--ui-color-warning, #eab308) 28%, var(--ui-color-surface-elevated));
  color: var(--ui-color-warning, #ca8a04);
  border: 1px solid var(--ui-color-warning, #ca8a04);
}
[data-ui-role="alert"][data-ui-variant="danger"] [data-ui-role="button"],
[data-ui-role="alert"][data-ui-variant="destructive"] [data-ui-role="button"] {
  background: color-mix(in srgb, var(--ui-color-danger) 28%, var(--ui-color-surface-elevated));
  color: var(--ui-color-danger);
  border: 1px solid var(--ui-color-danger);
}
CSS;
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
[data-ui-role="grid"] > [data-ui-role="button"] {
  align-self: start;
  justify-self: start;
  width: auto;
}
[data-ui-role="grid"] > [data-ui-role="button"][data-ui-layout="block"] {
  justify-self: stretch;
  width: 100%;
}
[data-ui-role="nav"] {
  display: flex;
  flex-wrap: wrap;
  gap: var(--ui-space-sm) var(--ui-space-md);
  font-size: var(--ui-font-size-sm);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="nav"] a {
  color: var(--ui-color-primary);
  text-decoration: none;
}
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
  border-radius: var(--ui-radius-md);
  min-height: 1em;
}
[data-ui-role="skeleton"][data-ui-variant="text"] {
  min-height: 1em;
  width: 100%;
}
[data-ui-role="skeleton"][data-ui-variant="rect"] {
  min-height: 4rem;
  width: 100%;
}
[data-ui-role="skeleton"][data-ui-variant="card"] {
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

    private function nativeOverlayRules(): string
    {
        return <<<'CSS'
dialog.ui-dialog,
[data-ui-role="modal"],
[data-ui-role="dialog"],
[data-ui-role="alert-dialog-content"] {
  background: var(--ui-overlay-surface);
  color: var(--ui-color-text);
  border: 1px solid var(--ui-overlay-border);
  border-radius: var(--ui-radius-lg);
  box-shadow: var(--ui-overlay-shadow);
  padding: var(--ui-space-lg);
  font-family: var(--ui-font-family-sans);
  max-width: min(32rem, calc(100vw - 2 * var(--ui-space-lg)));
}
dialog[open],
[data-ui-role="modal"][open],
[data-ui-role="dialog"][open] {
  z-index: var(--ui-z-modal);
}
dialog::backdrop {
  background: var(--ui-backdrop-color);
  backdrop-filter: blur(var(--ui-backdrop-blur));
}
[popover].ui-popover,
[data-ui-role="popover"] {
  background: var(--ui-overlay-surface);
  color: var(--ui-color-text);
  border: 1px solid var(--ui-overlay-border);
  border-radius: var(--ui-radius-md);
  box-shadow: var(--ui-overlay-shadow);
  padding: var(--ui-space-md);
  font-family: var(--ui-font-family-sans);
  z-index: var(--ui-z-popover);
  margin: 0;
}
:popover-open.ui-popover,
:popover-open[data-ui-role="popover"] {
  border-color: var(--ui-color-primary);
}
@supports (anchor-name: --ui-menu-trigger) {
  :popover-open.ui-popover,
  :popover-open[data-ui-role="popover"],
  [data-ui-role="menu"][popover]:popover-open {
    position-anchor: --ui-menu-trigger;
    position-area: block-end inline-start;
    margin-block-start: var(--ui-space-xs);
    position-try-fallbacks: flip-block, flip-inline;
    inset: unset;
  }
}
CSS;
    }

    private function anchorMenuRules(): string
    {
        return <<<'CSS'
[data-ui-anchor="trigger"] {
  anchor-name: --ui-menu-trigger;
}
[data-ui-role="menu"] {
  z-index: var(--ui-z-popover);
  background: var(--ui-overlay-surface);
  border: 1px solid var(--ui-overlay-border);
  border-radius: var(--ui-radius-md);
  box-shadow: var(--ui-overlay-shadow);
  padding: var(--ui-space-sm);
  min-width: 12rem;
  margin: 0;
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="menu"]:not([popover]) {
  position: absolute;
}
@supports (anchor-name: --ui-menu-trigger) {
  [data-ui-role="menu"]:not([popover]) {
    position: absolute;
    position-anchor: --ui-menu-trigger;
    position-area: block-end inline-start;
    margin-block-start: var(--ui-space-xs);
    position-try-fallbacks: flip-block, flip-inline;
  }
}
@supports not (anchor-name: --ui-menu-trigger) {
  [data-ui-role="menu"]:not([popover]) {
    inset-block-start: 100%;
    inset-inline-start: 0;
  }
}
CSS;
    }

    /**
     * symfinity/ux-blocks-extended — blocks.ext roles (025 T009/T024).
     */
    private function extendedRoleRules(): string
    {
        return <<<'CSS'
[data-ui-role="tabs"] {
  display: flex;
  flex-direction: column;
  gap: var(--ui-space-sm);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="tabs-list"] {
  display: flex;
  flex-wrap: wrap;
  gap: var(--ui-space-xs);
  border-block-end: 1px solid var(--ui-color-border);
  padding-block-end: var(--ui-space-xs);
}
[data-ui-role="tabs-trigger"] {
  padding: var(--ui-space-xs) var(--ui-space-sm);
  border-radius: var(--ui-radius-md);
  background: transparent;
  border: 1px solid transparent;
  cursor: pointer;
  font-family: inherit;
  font-size: var(--ui-font-size-sm);
  color: var(--ui-color-text-muted);
}
[data-ui-role="tabs-trigger"][aria-selected="true"] {
  color: var(--ui-color-text);
  background: var(--ui-color-surface-elevated);
  border-color: var(--ui-color-border);
}
[data-ui-role="tabs-content"] {
  padding-block-start: var(--ui-space-sm);
}
[data-ui-role="dropdown-menu"],
[data-ui-role="menubar"],
[data-ui-role="navigation-menu"],
[data-ui-role="combobox"],
[data-ui-role="context-menu"],
[data-ui-role="filter-chips"] {
  position: relative;
  display: inline-block;
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="dropdown-menu-content"],
[data-ui-role="menubar-content"],
[data-ui-role="navigation-menu-content"],
[data-ui-role="combobox-content"],
[data-ui-role="context-menu-content"],
[data-ui-role="hover-card-content"] {
  z-index: var(--ui-z-popover);
  background: var(--ui-overlay-surface);
  border: 1px solid var(--ui-overlay-border);
  border-radius: var(--ui-radius-md);
  box-shadow: var(--ui-overlay-shadow);
  padding: var(--ui-space-sm);
  min-width: 12rem;
  font-family: var(--ui-font-family-sans);
  font-size: var(--ui-font-size-sm);
}
[data-ui-role="dropdown-menu-item"],
[data-ui-role="menubar-item"],
[data-ui-role="navigation-menu-item"],
[data-ui-role="combobox-item"],
[data-ui-role="context-menu-item"] {
  display: block;
  width: 100%;
  padding: var(--ui-space-xs) var(--ui-space-sm);
  border: 0;
  border-radius: var(--ui-radius-sm);
  background: transparent;
  text-align: start;
  cursor: pointer;
  font-family: inherit;
  font-size: inherit;
  color: var(--ui-color-text);
}
[data-ui-role="dropdown-menu-item"]:hover,
[data-ui-role="menubar-item"]:hover,
[data-ui-role="navigation-menu-item"]:hover,
[data-ui-role="combobox-item"]:hover,
[data-ui-role="context-menu-item"]:hover,
[data-ui-role="dropdown-menu-item"]:focus-visible,
[data-ui-role="menubar-item"]:focus-visible,
[data-ui-role="navigation-menu-item"]:focus-visible,
[data-ui-role="combobox-item"]:focus-visible,
[data-ui-role="context-menu-item"]:focus-visible {
  background: var(--ui-color-surface-elevated);
  outline: 0;
}
[data-ui-role="alert-dialog-enhanced"] {
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="alert-dialog-trigger"],
[data-ui-role="dropdown-menu-trigger"],
[data-ui-role="drawer-trigger"],
[data-ui-role="sheet-trigger"],
[data-ui-role="context-menu-trigger"],
[data-ui-role="hover-card-trigger"] {
  cursor: pointer;
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="alert-dialog-title"],
[data-ui-role="drawer-title"],
[data-ui-role="sheet-title"] {
  font-family: var(--ui-font-family-sans);
  font-size: var(--ui-font-size-lg, 1.125rem);
  font-weight: 600;
  line-height: 1.3;
  color: var(--ui-color-text);
  margin: 0 0 var(--ui-space-sm);
}
[data-ui-role="alert-dialog-description"] {
  font-family: var(--ui-font-family-sans);
  font-size: var(--ui-font-size-sm);
  line-height: 1.5;
  color: var(--ui-color-text-muted);
  margin: 0 0 var(--ui-space-md);
}
[data-ui-role="alert-dialog-footer"] {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: flex-end;
  gap: var(--ui-space-sm);
  margin-block-start: var(--ui-space-md);
}
[data-ui-role="alert-dialog-action"],
[data-ui-role="alert-dialog-cancel"],
[data-ui-role="drawer-close"],
[data-ui-role="sheet-close"] {
  cursor: pointer;
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="drawer-header"],
[data-ui-role="sheet-header"] {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: var(--ui-space-sm);
  margin-block-end: var(--ui-space-md);
}
[data-ui-role="alert-dialog-content"] {
  max-width: min(28rem, calc(100vw - 2 * var(--ui-space-lg)));
}
[data-ui-role="drawer"],
[data-ui-role="sheet"],
[data-ui-role="sidebar"] {
  position: relative;
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="drawer-content"],
[data-ui-role="sheet-content"],
[data-ui-role="sidebar-content"] {
  background: var(--ui-overlay-surface);
  color: var(--ui-color-text);
  border: 1px solid var(--ui-overlay-border);
  box-shadow: var(--ui-overlay-shadow);
  padding: var(--ui-space-lg);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="hover-card"] {
  position: relative;
  display: inline-block;
}
[data-ui-role="slider"] {
  width: 100%;
  accent-color: var(--ui-color-primary);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="toggle"] {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: var(--ui-space-xs) var(--ui-space-sm);
  border-radius: var(--ui-radius-md);
  border: 1px solid var(--ui-color-border);
  background: var(--ui-color-surface);
  cursor: pointer;
  font-family: var(--ui-font-family-sans);
  font-size: var(--ui-font-size-sm);
}
[data-ui-role="toggle"][aria-pressed="true"] {
  background: var(--ui-color-primary);
  color: #fff;
  border-color: var(--ui-color-primary);
}
[data-ui-role="toggle-group"] {
  display: inline-flex;
  flex-wrap: wrap;
  gap: var(--ui-space-xs);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="toggle-group-item"] {
  padding: var(--ui-space-xs) var(--ui-space-sm);
  border: 1px solid var(--ui-color-border);
  border-radius: var(--ui-radius-md);
  background: var(--ui-color-surface);
  cursor: pointer;
  font-family: inherit;
  font-size: var(--ui-font-size-sm);
}
[data-ui-role="toggle-group-item"][aria-pressed="true"] {
  background: var(--ui-color-primary);
  color: #fff;
  border-color: var(--ui-color-primary);
}
[data-ui-role="calendar"] {
  display: grid;
  gap: var(--ui-space-xs);
  padding: var(--ui-space-sm);
  border: 1px solid var(--ui-color-border);
  border-radius: var(--ui-radius-md);
  background: var(--ui-overlay-surface);
  font-family: var(--ui-font-family-sans);
  font-size: var(--ui-font-size-sm);
}
[data-ui-role="calendar"] [data-ui-part="day"] {
  min-width: 2rem;
  min-height: 2rem;
  border: 0;
  border-radius: var(--ui-radius-sm);
  background: transparent;
  cursor: pointer;
}
[data-ui-role="calendar"] [data-ui-part="day"][aria-selected="true"] {
  background: var(--ui-color-primary);
  color: #fff;
}
[data-ui-role="date-picker"] {
  position: relative;
  display: inline-block;
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="date-picker-content"] {
  z-index: var(--ui-z-popover);
  margin-block-start: var(--ui-space-xs);
}
[data-ui-role="input-otp"] {
  display: flex;
  gap: var(--ui-space-xs);
  font-family: var(--ui-font-family-mono, monospace);
}
[data-ui-role="input-otp"] input {
  width: 2.5rem;
  height: 2.5rem;
  text-align: center;
  font-size: var(--ui-font-size-lg, 1.125rem);
  border: 1px solid var(--ui-color-border);
  border-radius: var(--ui-radius-md);
}
[data-ui-role="stacked-layout-interactive"] {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="stacked-layout-interactive"] [data-ui-part="nav"] {
  display: flex;
  flex-wrap: wrap;
  gap: var(--ui-space-sm);
  padding: var(--ui-space-md);
  border-block-end: 1px solid var(--ui-color-border);
}
[data-ui-role="command-palette"] {
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="command-palette"] [data-ui-part="input"] {
  width: 100%;
  padding: var(--ui-space-sm) var(--ui-space-md);
  border: 1px solid var(--ui-color-border);
  border-radius: var(--ui-radius-md);
  font-size: var(--ui-font-size-md);
}
[data-ui-role="command-palette"] [data-ui-part="list"] {
  max-height: 16rem;
  overflow: auto;
  margin-block-start: var(--ui-space-sm);
  border: 1px solid var(--ui-color-border);
  border-radius: var(--ui-radius-md);
  background: var(--ui-overlay-surface);
}
[data-ui-role="command-palette-item"] {
  display: block;
  width: 100%;
  padding: var(--ui-space-sm) var(--ui-space-md);
  border: 0;
  background: transparent;
  text-align: start;
  cursor: pointer;
  font-family: inherit;
}
[data-ui-role="command-palette-item"][aria-selected="true"],
[data-ui-role="command-palette-item"]:hover {
  background: var(--ui-color-surface-elevated);
}
[data-ui-role="toast"] {
  position: fixed;
  inset-block-end: var(--ui-space-lg);
  inset-inline-end: var(--ui-space-lg);
  z-index: var(--ui-z-toast, 1100);
  display: flex;
  flex-direction: column;
  gap: var(--ui-space-sm);
  max-width: 24rem;
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="toast-item"] {
  padding: var(--ui-space-md);
  border-radius: var(--ui-radius-md);
  border: 1px solid var(--ui-overlay-border);
  background: var(--ui-overlay-surface);
  box-shadow: var(--ui-overlay-shadow);
  font-size: var(--ui-font-size-sm);
}
[data-ui-role="resizable"] {
  display: flex;
  min-height: 8rem;
  border: 1px solid var(--ui-color-border);
  border-radius: var(--ui-radius-md);
  overflow: hidden;
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="resizable-panel"] {
  flex: 1 1 auto;
  padding: var(--ui-space-md);
  overflow: auto;
}
[data-ui-role="resizable-handle"] {
  flex: 0 0 0.375rem;
  cursor: col-resize;
  background: var(--ui-color-border);
}
[data-ui-role="data-table-chrome"],
[data-ui-role="data-table-chrome-interactive"] {
  display: flex;
  flex-direction: column;
  gap: var(--ui-space-sm);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="data-table-chrome-toolbar"] {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: var(--ui-space-sm);
}
[data-ui-role="carousel-interactive"] {
  position: relative;
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="carousel-interactive-viewport"] {
  display: flex;
  gap: var(--ui-space-md);
  overflow-x: auto;
  scroll-snap-type: x mandatory;
}
[data-ui-role="carousel-interactive-item"] {
  flex: 0 0 80%;
  scroll-snap-align: start;
  padding: var(--ui-space-md);
  border: 1px solid var(--ui-color-border);
  border-radius: var(--ui-radius-md);
}
[data-ui-role="rating"] {
  display: inline-flex;
  gap: var(--ui-space-xs);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="rating"] button {
  border: 0;
  background: transparent;
  cursor: pointer;
  font-size: 1.25rem;
  color: var(--ui-color-text-muted);
  padding: 0;
}
[data-ui-role="rating"] button[aria-pressed="true"] {
  color: var(--ui-color-warning, #eab308);
}
[data-ui-role="filter-chips"] {
  display: flex;
  flex-wrap: wrap;
  gap: var(--ui-space-xs);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="date-range-picker"] {
  position: relative;
  display: inline-flex;
  flex-wrap: wrap;
  align-items: center;
  gap: var(--ui-space-sm);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="date-range-picker-segments"] {
  display: inline-flex;
  align-items: center;
  gap: var(--ui-space-sm);
}
[data-ui-role="date-range-picker-segment"] {
  padding: var(--ui-space-xs) var(--ui-space-sm);
  border: 1px solid var(--ui-color-border);
  border-radius: var(--ui-radius-md);
  font-size: var(--ui-font-size-sm);
}
[data-ui-role="date-range-picker-content"] {
  z-index: var(--ui-z-popover);
  margin-block-start: var(--ui-space-xs);
  padding: var(--ui-space-md);
  border: 1px solid var(--ui-overlay-border);
  border-radius: var(--ui-radius-md);
  background: var(--ui-overlay-surface);
  box-shadow: var(--ui-overlay-shadow);
}
[data-ui-role="tags-input"] {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: var(--ui-space-xs);
  padding: var(--ui-space-xs);
  border: 1px solid var(--ui-color-border);
  border-radius: var(--ui-radius-md);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="tags-input-chip"] {
  display: inline-flex;
  align-items: center;
  gap: var(--ui-space-xs);
  padding: var(--ui-space-xs) var(--ui-space-sm);
  border-radius: var(--ui-radius-full);
  background: var(--ui-color-surface-elevated);
  font-size: var(--ui-font-size-sm);
}
[data-ui-role="tags-input-field"] {
  flex: 1 1 6rem;
  min-width: 6rem;
  border: 0;
  background: transparent;
  font: inherit;
  outline: none;
}
[data-ui-role="tree-view"] {
  font-family: var(--ui-font-family-sans);
  list-style: none;
  padding: 0;
  margin: 0;
}
[data-ui-role="tree-view-item"] {
  padding: var(--ui-space-xs) var(--ui-space-sm);
  cursor: pointer;
  border-radius: var(--ui-radius-sm);
}
[data-ui-role="tree-view-item"]:focus-visible,
[data-ui-role="tree-view-item"][aria-selected="true"] {
  background: var(--ui-color-surface-elevated);
}
[data-ui-role="filter-chip"] {
  display: inline-flex;
  align-items: center;
  gap: var(--ui-space-xs);
  padding: var(--ui-space-xs) var(--ui-space-sm);
  border-radius: var(--ui-radius-full);
  border: 1px solid var(--ui-color-border);
  background: var(--ui-color-surface-elevated);
  font-size: var(--ui-font-size-sm);
}
[data-ui-role="filter-chip"] button {
  border: 0;
  background: transparent;
  cursor: pointer;
  padding: 0;
  line-height: 1;
}
[data-ui-role="sidebar"] {
  display: grid;
  grid-template-columns: minmax(12rem, 16rem) 1fr;
  min-height: 12rem;
}
@media (max-width: 767px) {
  [data-ui-role="sidebar"] {
    grid-template-columns: 1fr;
  }
}
CSS;
    }

    /**
     * symfinity/ux-blocks-marketing — blocks.marketing section roles (026 T007).
     */
    private function marketingRoleRules(): string
    {
        return <<<'CSS'

[data-ui-role="hero"],
[data-ui-role="feature-section"],
[data-ui-role="cta-band"],
[data-ui-role="pricing-section"],
[data-ui-role="landing-page"],
[data-ui-role="testimonials"],
[data-ui-role="newsletter"],
[data-ui-role="footer"],
[data-ui-role="stats-band"],
[data-ui-role="logo-cloud"],
[data-ui-role="faq"],
[data-ui-role="team"],
[data-ui-role="content-section"],
[data-ui-role="bento-grid"],
[data-ui-role="banner"],
[data-ui-role="header-marketing"],
[data-ui-role="flyout-menu-marketing"],
[data-ui-role="error-page-404"],
[data-ui-role="comparison-section"],
[data-ui-role="integrations-section"],
[data-ui-role="cookie-consent"],
[data-ui-role="status-band"] {
  font-family: var(--ui-font-family-sans);
  color: var(--ui-color-text);
  box-sizing: border-box;
}
[data-ui-role="hero"],
[data-ui-role="landing-page"] > [data-ui-slot="hero"] {
  display: grid;
  gap: var(--ui-space-lg);
  padding-block: var(--ui-space-xl);
}
[data-ui-role="pricing-section"] {
  display: grid;
  gap: var(--ui-space-md);
  grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));
}
[data-ui-role="logo-cloud"] {
  display: flex;
  flex-wrap: wrap;
  gap: var(--ui-space-md);
  align-items: center;
  justify-content: center;
}
[data-ui-role="stats-band"] {
  display: grid;
  gap: var(--ui-space-md);
  grid-template-columns: repeat(auto-fit, minmax(8rem, 1fr));
}
[data-ui-role="comparison-section"] table {
  width: 100%;
  border-collapse: collapse;
}
[data-ui-role="comparison-section"] th,
[data-ui-role="comparison-section"] td {
  border: 1px solid var(--ui-color-border);
  padding: var(--ui-space-sm) var(--ui-space-md);
  text-align: center;
}
[data-ui-role="comparison-section"] th[data-ui-highlight="true"] {
  background: color-mix(in srgb, var(--ui-color-primary) 12%, var(--ui-color-surface-elevated));
}
[data-ui-role="integrations-section"] > div {
  display: grid;
  gap: var(--ui-space-md);
  grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr));
}
[data-ui-role="integrations-section"] article {
  display: flex;
  flex-direction: column;
  gap: var(--ui-space-sm);
  padding: var(--ui-space-md);
  border: 1px solid var(--ui-color-border);
  border-radius: var(--ui-radius-md);
}
[data-ui-role="cookie-consent"] {
  display: flex;
  flex-direction: column;
  gap: var(--ui-space-md);
  padding: var(--ui-space-lg);
  border: 1px solid var(--ui-color-border);
  border-radius: var(--ui-radius-lg);
  background: var(--ui-color-surface-elevated);
}
[data-ui-role="cookie-consent"] ul {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: var(--ui-space-sm);
}
[data-ui-role="status-band"] {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: var(--ui-space-md);
  padding: var(--ui-space-md) var(--ui-space-lg);
  border: 1px solid var(--ui-color-border);
  border-radius: var(--ui-radius-md);
}
[data-ui-role="status-band"][data-ui-status-tone="operational"] {
  border-color: var(--ui-color-success);
}
[data-ui-role="status-band"][data-ui-status-tone="degraded"] {
  border-color: var(--ui-color-warning, #ca8a04);
}
[data-ui-role="status-band"][data-ui-status-tone="outage"] {
  border-color: var(--ui-color-danger);
}
[data-ui-role="status-band"][data-ui-status-tone="maintenance"] {
  border-color: var(--ui-color-info);
}
[data-ui-role="status-band"] ul {
  display: flex;
  flex-wrap: wrap;
  gap: var(--ui-space-md);
  list-style: none;
  margin: 0;
  padding: 0;
}
[data-ui-role="bento-grid"] {
  display: grid;
  gap: var(--ui-space-md);
  grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr));
}
[data-ui-role="landing-page"] {
  display: flex;
  flex-direction: column;
  gap: var(--ui-space-xl);
}
[data-ui-role="flyout-menu-marketing"] ul {
  display: flex;
  flex-wrap: wrap;
  gap: var(--ui-space-sm);
  list-style: none;
  margin: 0;
  padding: 0;
}
[data-ui-role="faq"] details {
  border-block-end: 1px solid var(--ui-color-border);
  padding-block: var(--ui-space-sm);
}
CSS;
    }

    /**
     * Overlay panel positioning for ux-blocks-extended shipped roles (025 T024).
     */
    private function extendedOverlayPanelRules(): string
    {
        return <<<'CSS'

[data-ui-role="drawer-content"]:not([hidden]),
[data-ui-role="sheet-content"]:not([hidden]) {
  position: fixed;
  z-index: var(--ui-z-modal);
  overflow: auto;
  box-sizing: border-box;
}
[data-ui-role="drawer-content"][data-ui-side="bottom"],
[data-ui-role="sheet-content"][data-ui-side="bottom"] {
  inset-block-end: 0;
  inset-inline: 0;
  max-block-size: min(24rem, 90vh);
  width: 100%;
  border-block-start: 1px solid var(--ui-overlay-border);
  border-radius: var(--ui-radius-lg) var(--ui-radius-lg) 0 0;
}
[data-ui-role="drawer-content"][data-ui-side="top"],
[data-ui-role="sheet-content"][data-ui-side="top"] {
  inset-block-start: 0;
  inset-inline: 0;
  max-block-size: min(24rem, 90vh);
  width: 100%;
  border-block-end: 1px solid var(--ui-overlay-border);
  border-radius: 0 0 var(--ui-radius-lg) var(--ui-radius-lg);
}
[data-ui-role="drawer-content"][data-ui-side="right"],
[data-ui-role="sheet-content"][data-ui-side="right"] {
  inset-block: 0;
  inset-inline-end: 0;
  max-inline-size: min(24rem, 100vw);
  width: 100%;
  border-inline-start: 1px solid var(--ui-overlay-border);
}
[data-ui-role="drawer-content"][data-ui-side="left"],
[data-ui-role="sheet-content"][data-ui-side="left"] {
  inset-block: 0;
  inset-inline-start: 0;
  max-inline-size: min(24rem, 100vw);
  width: 100%;
  border-inline-end: 1px solid var(--ui-overlay-border);
}
[data-ui-role="context-menu-content"]:not([hidden]) {
  position: fixed;
  z-index: var(--ui-z-popover);
}
[data-ui-role="hover-card-content"]:not([hidden]) {
  position: absolute;
  z-index: var(--ui-z-popover);
  margin-block-start: var(--ui-space-xs);
}
CSS;
    }

    /**
     * symfinity/ux-blocks-core — blocks.image (047-r2). Emitted for all schema versions
     * so Workshop default (v1) lineage previews show fluid/thumbnail/rounded styling.
     */
    private function imageRoleRules(): string
    {
        return <<<'CSS'

[data-ui-role="image"] {
  display: block;
  max-width: 100%;
  height: auto;
}
[data-ui-role="image"][data-ui-variant="fluid"] {
  width: 100%;
}
[data-ui-role="image"][data-ui-variant="thumbnail"] {
  width: auto;
  max-width: 12.5rem;
  height: auto;
}
[data-ui-role="image"][data-ui-variant="rounded"] {
  border-radius: var(--ui-radius-lg);
  overflow: hidden;
}
CSS;
    }

    /**
     * symfinity/ux-blocks-core — blocks.auth-layout. Emitted for all schema versions
     * so Workshop default (v1) lineage previews center auth forms with stacked children.
     */
    private function authLayoutRoleRules(): string
    {
        return <<<'CSS'

[data-ui-role="auth-layout"] {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: var(--ui-space-lg);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="auth-layout"] > * {
  width: min(24rem, 100%);
  margin-block-end: 0.5rem;
}
[data-ui-role="auth-layout"] > *:last-child {
  margin-block-end: 0;
}
CSS;
    }

    /**
     * symfinity/ux-blocks-core — blocks.avatar. Emitted for all schema versions
     * so Workshop default (v1) lineage previews show initials/image chrome.
     */
    private function avatarRoleRules(): string
    {
        return <<<'CSS'

[data-ui-role="avatar"] {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: var(--ui-radius-full);
  overflow: hidden;
  background: var(--ui-color-surface-elevated);
  border: 1px solid var(--ui-color-border);
  color: var(--ui-color-text);
  font-family: var(--ui-font-family-sans);
  font-size: var(--ui-font-size-sm);
  font-weight: 600;
  line-height: 1;
  box-sizing: border-box;
}
[data-ui-role="avatar"][data-ui-size="sm"] {
  width: 2rem;
  height: 2rem;
  font-size: var(--ui-font-size-xs, 0.75rem);
}
[data-ui-role="avatar"][data-ui-size="lg"] {
  width: 3rem;
  height: 3rem;
  font-size: var(--ui-font-size-md);
}
[data-ui-role="avatar"][data-ui-variant="primary"] {
  background: var(--ui-color-primary);
  border-color: var(--ui-color-primary);
  color: #fff;
}
[data-ui-role="avatar"][data-ui-variant="secondary"] {
  background: var(--ui-color-secondary);
  border-color: var(--ui-color-secondary);
  color: #fff;
}
[data-ui-role="avatar"][data-ui-variant="tertiary"] {
  background: var(--ui-color-tertiary);
  border-color: var(--ui-color-tertiary);
  color: #fff;
}
[data-ui-role="avatar"][data-ui-variant="destructive"],
[data-ui-role="avatar"][data-ui-variant="danger"] {
  background: var(--ui-color-danger);
  border-color: var(--ui-color-danger);
  color: #fff;
}
[data-ui-role="avatar"][data-ui-variant="success"] {
  background: var(--ui-color-success);
  border-color: var(--ui-color-success);
  color: #fff;
}
[data-ui-role="avatar"][data-ui-variant="info"] {
  background: var(--ui-color-info);
  border-color: var(--ui-color-info);
  color: #fff;
}
[data-ui-role="avatar"][data-ui-variant="warning"] {
  background: var(--ui-color-warning);
  border-color: var(--ui-color-warning);
  color: #fff;
}
[data-ui-role="avatar"] img,
[data-ui-role="avatar"] [data-ui-role="image"] {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: inherit;
}
CSS;
    }

    /**
     * symfinity/ux-blocks-core — blocks.badge. Emitted for all schema versions
     * so Workshop default (v1) lineage previews show pill chrome + semantic variants.
     */
    private function badgeRoleRules(): string
    {
        return <<<'CSS'

[data-ui-role="badge"] {
  display: inline-flex;
  align-items: center;
  padding: 0.125rem var(--ui-space-sm);
  font-size: var(--ui-font-size-xs, 0.75rem);
  font-weight: 600;
  border-radius: var(--ui-radius-full);
  background: var(--ui-color-surface-elevated);
  border: 1px solid var(--ui-color-border);
  color: var(--ui-color-text);
  font-family: var(--ui-font-family-sans);
  line-height: 1.25;
  box-sizing: border-box;
}
[data-ui-role="badge"][data-ui-variant="default"],
[data-ui-role="badge"][data-ui-variant="primary"] {
  background: var(--ui-color-primary);
  border-color: var(--ui-color-primary);
  color: #fff;
}
[data-ui-role="badge"][data-ui-variant="secondary"] {
  background: var(--ui-color-secondary);
  border-color: var(--ui-color-secondary);
  color: #fff;
}
[data-ui-role="badge"][data-ui-variant="outline"] {
  background: transparent;
  color: var(--ui-color-text);
  border-color: var(--ui-color-border);
}
[data-ui-role="badge"][data-ui-variant="destructive"],
[data-ui-role="badge"][data-ui-variant="danger"] {
  background: var(--ui-color-danger);
  border-color: var(--ui-color-danger);
  color: #fff;
}
[data-ui-role="badge"][data-ui-variant="success"] {
  background: var(--ui-color-success);
  border-color: var(--ui-color-success);
  color: #fff;
}
[data-ui-role="badge"][data-ui-variant="info"] {
  background: var(--ui-color-info);
  border-color: var(--ui-color-info);
  color: #fff;
}
[data-ui-role="badge"][data-ui-variant="warning"] {
  background: var(--ui-color-warning);
  border-color: var(--ui-color-warning);
  color: #fff;
}
[data-ui-role="badge"][data-ui-variant="ghost"] {
  background: transparent;
  border-color: transparent;
  color: var(--ui-color-text-muted);
}
CSS;
    }

    /**
     * symfinity/ux-blocks-core — blocks.breadcrumb. Emitted for all schema versions
     * so Workshop default (v1) lineage previews show trail chrome + dividers.
     *
     * Divider tokens mirror Bootstrap 5.3 (--bs-breadcrumb-divider) via --ui-breadcrumb-divider.
     */
    private function breadcrumbRoleRules(): string
    {
        return <<<'CSS'

[data-ui-role="breadcrumb"] {
  --ui-breadcrumb-divider: "/";
  --ui-breadcrumb-divider-color: var(--ui-color-text-muted);
  --ui-breadcrumb-item-padding-x: 0.5rem;
  margin-block-end: var(--ui-space-md);
  font-size: var(--ui-font-size-sm);
  font-family: var(--ui-font-family-sans);
  color: var(--ui-color-text);
}
[data-ui-role="breadcrumb"][data-ui-divider="gt"] {
  --ui-breadcrumb-divider: ">";
}
[data-ui-role="breadcrumb"][data-ui-divider="chevron"] {
  --ui-breadcrumb-divider: "›";
}
[data-ui-role="breadcrumb"][data-ui-divider="none"] {
  --ui-breadcrumb-divider: "";
}
[data-ui-role="breadcrumb"] ol {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  list-style: none;
  padding: 0;
  margin: 0;
}
[data-ui-role="breadcrumb"] ol > li {
  display: inline-flex;
  align-items: center;
}
[data-ui-role="breadcrumb"] ol > :not(:first-child)::before {
  display: inline-block;
  padding-inline: var(--ui-breadcrumb-item-padding-x);
  color: var(--ui-breadcrumb-divider-color);
  content: var(--ui-breadcrumb-divider);
}
[data-ui-role="breadcrumb"] [data-ui-role="link"] {
  color: var(--ui-color-primary);
  text-decoration: none;
}
[data-ui-role="breadcrumb"] [data-ui-role="link"]:hover {
  text-decoration: underline;
}
[data-ui-role="breadcrumb"] ol > li[aria-current="page"],
[data-ui-role="breadcrumb"] ol > li:last-child:not(:has([data-ui-role="link"])) {
  color: var(--ui-breadcrumb-divider-color);
}
CSS;
    }

    private function v1CoreRoleRules(): string
    {
        return <<<'CSS'
[data-ui-role="scroll-area"] {
  overflow: auto;
  max-block-size: 100%;
  -webkit-overflow-scrolling: touch;
}
[data-ui-role="aspect-ratio"] {
  position: relative;
  width: 100%;
  max-width: 100%;
  overflow: hidden;
  box-sizing: border-box;
}
[data-ui-role="aspect-ratio"] > * {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  max-width: 100%;
  max-height: 100%;
  object-fit: cover;
  object-position: center;
  display: block;
}
[data-ui-role="aspect-ratio"][data-ui-ratio="16/9"] { aspect-ratio: 16 / 9; }
[data-ui-role="aspect-ratio"][data-ui-ratio="4/3"] { aspect-ratio: 4 / 3; }
[data-ui-role="aspect-ratio"][data-ui-ratio="3/4"] { aspect-ratio: 3 / 4; }
[data-ui-role="aspect-ratio"][data-ui-ratio="1/1"] { aspect-ratio: 1 / 1; }
[data-ui-role="aspect-ratio"] > [data-ui-role="image"] {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  max-width: none;
  object-fit: cover;
  object-position: center;
}
[data-ui-role="aspect-ratio"] > [data-ui-role="image"][data-ui-variant="rounded"] {
  border-radius: var(--ui-radius-lg);
}
[data-ui-role="divider"] {
  border: 0;
  border-block-start: 1px solid var(--ui-color-border);
  margin: var(--ui-space-md) 0;
}
[data-ui-role="divider"][data-ui-variant="vertical"] {
  border-block-start: 0;
  border-inline-start: 1px solid var(--ui-color-border);
  width: 1px;
  min-height: 1rem;
  margin: 0 var(--ui-space-md);
}
[data-ui-role="tooltip"] {
  position: relative;
  display: inline-flex;
}
[data-ui-role="spinner"] {
  display: inline-block;
  width: 1.25rem;
  height: 1.25rem;
  border: 2px solid var(--ui-color-border);
  border-block-start-color: var(--ui-color-primary);
  border-radius: var(--ui-radius-full);
  animation: ui-spin 0.75s linear infinite;
}
[data-ui-role="spinner"][data-ui-size="sm"] {
  width: 1rem;
  height: 1rem;
  border-width: 2px;
}
[data-ui-role="spinner"][data-ui-size="lg"] {
  width: 2rem;
  height: 2rem;
  border-width: 3px;
}
[data-ui-role="spinner"][data-ui-density="block"] {
  display: block;
  margin-inline: auto;
}
[data-ui-role="collapsible"] {
  display: block;
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="collapsible"][data-ui-state="closed"] [data-ui-role="collapsible-content"]:not([hidden]) {
  display: none;
}
[data-ui-role="collapsible-trigger"] {
  cursor: pointer;
  font-family: inherit;
}
[data-ui-role="collapsible-content"] {
  padding-block-start: var(--ui-space-sm);
}
@keyframes ui-spin {
  to { transform: rotate(360deg); }
}
[data-ui-role="progress"] {
  width: 100%;
  height: 0.5rem;
  accent-color: var(--ui-color-primary);
}
[data-ui-role="navbar"] {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: var(--ui-space-md);
  padding: var(--ui-space-sm) var(--ui-space-md);
  font-family: var(--ui-font-family-sans);
  border-block-end: 1px solid var(--ui-color-border);
}
[data-ui-role="navbar"] nav {
  display: flex;
  flex-wrap: wrap;
  gap: var(--ui-space-sm) var(--ui-space-md);
}
[data-ui-role="pagination"] {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: var(--ui-space-xs);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="list"] {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: var(--ui-space-sm);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="accordion"] details {
  border: 1px solid var(--ui-color-border);
  border-radius: var(--ui-radius-md);
  padding: var(--ui-space-sm) var(--ui-space-md);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="accordion"] details + details {
  margin-block-start: var(--ui-space-sm);
}
[data-ui-role="steps"] {
  display: flex;
  flex-wrap: wrap;
  gap: var(--ui-space-md);
  font-family: var(--ui-font-family-sans);
  font-size: var(--ui-font-size-sm);
}
[data-ui-role="link"] {
  color: var(--ui-color-primary);
  text-decoration: underline;
  font-family: var(--ui-font-family-sans);
  cursor: pointer;
}
[data-ui-role="link"][data-ui-variant="muted"] {
  color: var(--ui-color-text-muted);
}
[data-ui-role="switch"] {
  accent-color: var(--ui-color-primary);
  width: 2.5rem;
  height: 1.25rem;
  cursor: pointer;
}
[data-ui-role="input-group"] {
  display: flex;
  align-items: stretch;
  width: 100%;
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="input-group"] > * {
  margin-block-end: 0;
}
[data-ui-role="file-input"] {
  font-family: var(--ui-font-family-sans);
  font-size: var(--ui-font-size-sm);
  color: var(--ui-color-text);
}
[data-ui-role="fieldset"] {
  border: 1px solid var(--ui-color-border);
  border-radius: var(--ui-radius-md);
  padding: var(--ui-space-md);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="fieldset"] legend {
  padding: 0 var(--ui-space-xs);
  font-weight: 600;
}
[data-ui-role="description-list"] {
  display: grid;
  gap: var(--ui-space-sm);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="description-list"] dt {
  font-weight: 600;
  color: var(--ui-color-text);
}
[data-ui-role="description-list"] dd {
  margin: 0 0 var(--ui-space-sm);
  color: var(--ui-color-text-muted);
}
[data-ui-role="stat"] {
  display: flex;
  flex-direction: column;
  gap: var(--ui-space-xs);
  padding: var(--ui-space-md);
  border: 1px solid var(--ui-color-border);
  border-radius: var(--ui-radius-md);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="timeline"] {
  display: flex;
  flex-direction: column;
  gap: var(--ui-space-md);
  border-inline-start: 2px solid var(--ui-color-border);
  padding-inline-start: var(--ui-space-md);
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="carousel"] {
  display: flex;
  gap: var(--ui-space-md);
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  -webkit-overflow-scrolling: touch;
}
[data-ui-role="carousel"] > * {
  flex: 0 0 auto;
  scroll-snap-align: start;
  margin-block-end: 0;
}
[data-ui-role="kbd"] {
  display: inline-block;
  padding: 0.125rem var(--ui-space-xs);
  font-family: var(--ui-font-family-mono, monospace);
  font-size: var(--ui-font-size-xs, 0.75rem);
  border: 1px solid var(--ui-color-border);
  border-radius: var(--ui-radius-sm);
  background: var(--ui-color-surface-elevated);
}
[data-ui-role="button-group"] {
  display: inline-flex;
  flex-wrap: wrap;
  gap: 0;
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="button-group"] > [data-ui-role="button"] {
  margin-block-end: 0;
  border-radius: 0;
}
[data-ui-role="button-group"] > [data-ui-role="button"]:first-child {
  border-start-start-radius: var(--ui-radius-md);
  border-end-start-radius: var(--ui-radius-md);
}
[data-ui-role="button-group"] > [data-ui-role="button"]:last-child {
  border-start-end-radius: var(--ui-radius-md);
  border-end-end-radius: var(--ui-radius-md);
}
[data-ui-role="page-heading"] {
  font-family: var(--ui-font-family-sans);
  font-size: var(--ui-font-size-xl, 1.875rem);
  font-weight: 700;
  line-height: 1.2;
  color: var(--ui-color-text);
  margin: 0 0 var(--ui-space-md);
}
[data-ui-role="section-heading"] {
  font-family: var(--ui-font-family-sans);
  font-size: var(--ui-font-size-lg, 1.25rem);
  font-weight: 600;
  line-height: 1.3;
  color: var(--ui-color-text);
  margin: 0 0 var(--ui-space-sm);
}
[data-ui-role="dashboard-shell"] {
  display: grid;
  grid-template-columns: minmax(12rem, 16rem) 1fr;
  min-height: 100vh;
  font-family: var(--ui-font-family-sans);
}
[data-ui-role="dashboard-shell"] > * {
  margin-block-end: 0;
}
@media (max-width: 767px) {
  [data-ui-role="dashboard-shell"] {
    grid-template-columns: 1fr;
  }
}
@media (prefers-reduced-motion: reduce) {
  [data-ui-role="spinner"] {
    animation: none;
    opacity: 0.7;
  }
}
CSS;
    }

    private function scrollAndLoadingRules(bool $scrollMotion): string
    {
        $css = <<<'CSS'
[data-ui-defer="cv"] {
  content-visibility: auto;
  contain-intrinsic-size: auto 500px;
}
[data-ui-role="field-group"]:has(:invalid) {
  border-color: var(--ui-color-danger);
}
[data-ui-role="field-group"]:has(:focus-visible) {
  outline: 0;
  box-shadow: 0 0 var(--ui-focus-ring-blur) var(--ui-focus-ring-width) color-mix(in srgb, var(--ui-color-focus) calc(var(--ui-focus-ring-opacity) * 100%), transparent);
}
[data-ui-role="card"]:has(input:checked) {
  background: var(--ui-color-surface-elevated);
  border-color: var(--ui-color-primary);
}
[data-ui-role="nav"]:has([aria-current="page"]) [aria-current="page"] {
  font-weight: var(--ui-font-weight-semibold);
  color: var(--ui-color-primary);
}
CSS;

        if ($scrollMotion) {
            $css .= <<<'CSS'

[data-ui-scroll-reveal] {
  animation: ui-fade-in linear both;
  animation-timeline: view();
  animation-range: entry 0% cover 40%;
}
@media (prefers-reduced-motion: reduce) {
  [data-ui-scroll-reveal] {
    animation: none;
    opacity: 1;
  }
}
CSS;
        }

        return $css;
    }
}
