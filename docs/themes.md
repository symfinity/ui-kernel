# UI Kernel themes (package)

Symfony **Twig theme** (template paths, `twig.theme` in FrameworkBundle) is unrelated to Chameleon **UI Kernel themes** (`data-theme`, `ThemeRegistry`). See [Symfony Twig theming](https://symfony.com/doc/current/templates.html#twig-theme-configuration).

Normative contracts: [themes](../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/themes.md), [baseline-themes](../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/baseline-themes.md), [theme-token-engine](../../../../specs/symfinity/symfinity/9-ui-kernel-theme-tokens/contracts/theme-token-engine.md).

## SSOT (code)

| File | Role |
|------|------|
| `src/Token/ThemeConfig.php` | Built-in theme ids, labels, preset, tone, **palette refs** + **`ThemePaletteRecipe`** per theme |
| `src/Token/ThemePaletteRecipe.php` | Per-theme hue bases + mono tone tints (light/dark differ per theme id) |
| `src/Token/PaletteScaleAnchors.php` | Optional sparse hex overrides (empty by default) |
| `src/Token/PaletteRefGrammar.php` | Validates contract refs (018) |
| `src/Token/PaletteGenerator.php` | Resolves refs using the active theme recipe (shared lightness curve) |
| `src/Token/ThemeTokenResolver.php` | Merge preset presets + semantic colours → `DesignTokenSet` |
| `src/Token/PresetRegistry.php` | Kiroshi / Semantic / Utility — space, radius, type, shadow, motion |
| `src/Theme/ThemeCatalog.php` | Loads configs through resolver → `DefinedTheme` |
| `src/Theme/LayoutProfile.php` | Layout preset enum; delegates layout tokens to registry |
| `src/Theme/DefinedTheme.php` | Resolved theme + schema version |
| `src/Theme/ThemeRegistry.php` | Runtime registry (optional `UserTokenSet` merge) |
| `src/Token/ThemeTokenSchema.php` | Required keys for schema `1.0` / `2.0` (incl. `--ui-overlay-*`, `--ui-backdrop-*` at **2.0**) |
| `src/Profile/SystemProfile.php` | Structural breakpoints, columns, container widths (default `chameleon-default`) |
| `src/Profile/SystemProfileRegistry.php` | Resolves profile from `symfinity.ui_kernel.system_profile` config |
| `src/Css/CssGenerator.php` | Theme vars + profile z-index/keyframes + layout roles at schema `2.0` |

Add or change a theme in **`ThemeConfig` only** — palette refs, **`paletteRecipe()`** (hue bases + mono tone params), not raw hex. The same ref (e.g. `blue.600`) resolves differently per theme. Optional sparse overrides: `PaletteScaleAnchors`. Inspired-by preset lives in this doc only.

## System profile (structural layout)

Appearance tokens stay under **`LayoutProfile`** / theme resolver (**009**). Breakpoints, z-index ladder, global keyframes, and schema `2.0` layout roles (`grid`, `stack`, `skeleton`) come from **`SystemProfile`** — normative contract [system-profile](../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/system-profile.md).

Override breakpoint px or container max-widths via config (not `user_tokens`):

```yaml
symfinity:
    ui_kernel:
        system_profile:
            breakpoints:
                md: 800
```

When a Symfony cache pool keys generated CSS, include `systemProfileId` and `profileHash` from `CssGenerator::cacheKeyParts()`.

Light/dark pairs share one `LayoutProfile`; **semantic colour refs** differ between e.g. `semantic` and `semantic-dark`.

## Schema

Built-in themes target **schema `2.0`** (tertiary, warning, info, focus, overlay, skeleton, shadows, motion, focus-ring tokens). `CssGenerator` accepts `schemaVersion` for compatibility snapshots.

### Overlay tokens (**016**)

Resolved per theme (not palette refs) — see [native-overlay-css](../../../../specs/symfinity/symfinity/16-ui-kernel-final-css/contracts/native-overlay-css.md):

| Token | Source |
|-------|--------|
| `--ui-overlay-surface` | `--ui-color-surface-elevated` |
| `--ui-overlay-border` | `--ui-color-border` |
| `--ui-overlay-shadow` | `--ui-shadow-lg` |
| `--ui-backdrop-color` | `--ui-color-overlay` |
| `--ui-backdrop-blur` | Theme default (`0`; `6px` on semantic pair for marketing) |

Z-index for modals/popovers uses profile `--ui-z-*` only — never literals in theme PHP.

### Scroll motion flag

| Theme | `scrollMotion` |
|---------|----------------|
| `semantic`, `semantic-dark` | `true` — emits `[data-ui-scroll-reveal]` scroll-timeline rules |
| All others | `false` |

Disabled under `prefers-reduced-motion: reduce`. Normative: [scroll-and-loading-css](../../../../specs/symfinity/symfinity/16-ui-kernel-final-css/contracts/scroll-and-loading-css.md).

## User overrides

Partial overrides via Symfony config (`symfinity.ui_kernel.user_tokens`) — see `config/packages/symfinity_ui_kernel.yaml`. Unknown keys rejected at merge time.

Deploy-time brand (Face or any host) — **no** palette DB in kernel; override public `--ui-color-*` only:

```yaml
symfinity:
    ui_kernel:
        default_theme: semantic
        user_tokens:
            '--ui-color-primary': '#1a4fd6'
            '--ui-color-secondary': '#6c757d'
```

Quickstart: [018 palette SSOT quickstart](../../../../specs/symfinity/symfinity/18-ui-kernel-palette-ssot/quickstart.md).

## Layout profiles (lineage)

| Profile | Spacing rhythm | Radius | Type base | Motion |
|---------|----------------|--------|-----------|--------|
| **Kiroshi** | Tight (`md` 0.625rem) | Sharp (0) | Slightly compact (0.9375rem) | 150ms |
| **Semantic** | Roomy (`xl` 3rem) | Rounded (md 0.375rem) | Classic 1rem | 200ms |
| **Utility** | Compact mid (`md` 0.75rem) | Subtle (md 0.25rem) | Dense (md 0.875rem) | 150ms |

Showcase stack gap, button padding, card padding, and form controls read CSS variables — carousel theme change affects **layout** and palette.

## Kiroshi (`default`, `dark`)

**Lineage:** `LayoutProfile::Kiroshi` · **Spice:** `warm`

**Visual inspiration:** Night City palette cues from the public [Cyberpunk 2077](https://www.cyberpunk.net/) site. Symfinity **inspired-by** styling only.

| Id | Label | Surface (resolved) |
|----|-------|---------------------|
| `default` | Kiroshi | Neon yellow field |
| `dark` | Kiroshi dark | Near-black field |

## Semantic & Utility stacks

| Ids | Layout profile | Spice | Inspired-by (colours only) |
|-----|----------------|-------|----------------------------|
| `semantic`, `semantic-dark` | `LayoutProfile::Semantic` | `cool` | Bootstrap 5.3 default palette |
| `utility`, `utility-dark` | `LayoutProfile::Utility` | `cool` | Tailwind CSS default slate/blue/red/green |

**Rhythm** (space, radius, type scale, motion) stays on the preset preset — theme swap changes **semantic role colours** only, not layout profile tokens.

### Inspired-by colour mapping (semantic roles → reference hex)

Values resolve through `ThemeConfig` palette refs and `PaletteGenerator` — **no** `--bs-*` or Tailwind utility class names in generated CSS.

| Symfinity role | Semantic light | Semantic dark | Utility light | Utility dark |
|----------------|----------------|---------------|---------------|--------------|
| `primary` | `#0d6efd` | `#6ea8fe` | `#3b82f6` | `#60a5fa` |
| `secondary` | `#6c757d` | `#adb5bd` | `#64748b` | `#94a3b8` |
| `surface` | `#f8f9fa` | `#212529` | `#f8fafc` | `#0f172a` |
| `surface_elevated` | `#ffffff` | `#2b3035` | `#ffffff` | `#1e293b` |
| `text` | `#212529` | `#dee2e6` | `#0f172a` | `#f1f5f9` |
| `text_muted` | `#6c757d` | `#adb5bd` | `#64748b` | `#94a3b8` |
| `border` | `#dee2e6` | `#495057` | `#e2e8f0` | `#334155` |
| `danger` | `#dc3545` | `#ea868f` | `#ef4444` | `#f87171` |
| `success` | `#198754` | `#75b798` | `#22c55e` | `#4ade80` |
| `warning` | `#ffc107` | `#ffda6a` | `#f59e0b` | `#f59e0b` |
| `info` | `#0dcaf0` | `#6edff6` | `#0ea5e9` | `#38bdf8` |
| `tertiary` | `#6f42c1` | `#a98eda` | `#8b5cf6` | `#a78bfa` |

Kiroshi (`default` / `dark`) keeps the neon Night City palette; `tertiary` uses hot-pink accent (`pink.500` / `pink.400`) distinct from `danger` and cyan `secondary`.

## Demo page choreography (layout roles)

Normative selectors: [role-rules](../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/role-rules.md) schema **2.0** · token `--ui-grid-gap` in [theme-token-schema](../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/theme-token-schema.md).

| Role | CSS in `CssGenerator` | Twig in `ux-blocks-core` | Demo usage |
|------|-------------------------|--------------------------|------------|
| `grid` | yes (`layoutRoleRules`) | `Grid`, `Grid:Cell` | Gallery columns, omnia mosaic |
| `stack` | yes | `Stack` | Page shell, section vertical rhythm |
| `grid-container` | yes (profile max-width) | raw `data-ui-role` | `base.html.twig` page width |
| `nav` | yes | raw `data-ui-role` | Demo pack links, theme jump aside |
| `card` | yes (v0) | `Card` | Demo sections, index pack list |

**MUST NOT** duplicate grid/stack/nav spacing in demo `extra_styles` when a layout role covers it. Acceptable demo-only CSS: theme-jump pill chrome (`.demo-theme-jump`), kernel overlay fixture helpers (menu shell, scroll spacer).

**Component block rhythm:** every `[data-ui-role]` root defaults to `margin-block-end: var(--ui-space-md)` (Kiroshi `0.625rem`) — see [rhythm contract](../../../../specs/symfinity/symfinity/9-ui-kernel-theme-tokens/contracts/rhythm.md) § Component block rhythm. Stacks/grids use `gap` for children; nested roles inside `field`/`alert`/`card` are exempt.

**Still planned (registry):** `skeleton` Twig component — CSS exists; loading demo uses raw hooks.

## Fonts

System stacks only in v1. Optional `symfinity/font-manager` pairing: [font-manager-pairing](./font-manager-pairing.md).
