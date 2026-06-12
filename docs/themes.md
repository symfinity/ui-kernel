# UI Kernel themes (package)

Symfony **Twig theme** (template paths, `twig.theme` in FrameworkBundle) is unrelated to Chameleon **UI Kernel themes** (`data-theme`, `ThemeRegistry`). See [Symfony Twig theming](https://symfony.com/doc/current/templates.html#twig-theme-configuration).

Normative contracts: [theme-vocabulary](../../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/theme-vocabulary.md), themes, baseline-themes, theme-token-engine.

## Config SSOT (YAML)

| File | Role |
|------|------|
| `config/packages/symfinity_ui_kernel.yaml` | `contract.palette`, `generator.palette` |
| `config/themes/*.yaml` | `symfinity_ui_kernel.themes` — six built-ins across three files |
| App `config/packages/symfinity_ui_kernel.yaml` | `default_theme`, `user_tokens`, `system_profile` only |

See [configuration.md](configuration.md).

## SSOT (code)

| File | Role |
|------|------|
| `src/Token/BuiltinThemeCatalog.php` | Loads `symfinity_ui_kernel.themes` from `config/themes/*.yaml` via `ThemeYamlNormalizer` |
| `src/Token/PaletteCatalog.php` | Palette contract + `lineages()` donors |
| `src/Token/ThemeConfig.php` | Built-in theme ids, labels, tone, **palette refs** + **`ThemePaletteRecipe`** per variant |
| `src/Token/ThemePaletteRecipe.php` | Per-theme hue bases + mono tone tints (light/dark differ per theme id) |
| `src/Token/PaletteScaleAnchors.php` | Optional sparse hex overrides (empty by default) |
| `src/Token/PaletteRefGrammar.php` | Validates contract refs (018) |
| `src/Token/PaletteGenerator.php` | Resolves refs using the active theme recipe (shared lightness curve) |
| `src/Token/ThemeTokenMap.php` | Short YAML keys ↔ `--ui-*`; validates lineage `tokens` |
| `src/Token/ThemeTokenResolver.php` | Merge YAML appearance tokens + semantic colours → `DesignTokenSet` |
| `src/Token/PresetRegistry.php` | Fallback layout tokens when appearance map is empty (tests / legacy) |
| `src/Theme/ThemeCatalog.php` | Loads configs through resolver → `DefinedTheme` |
| `src/Theme/LayoutProfile.php` | Layout preset enum; delegates layout tokens to registry |
| `src/Theme/DefinedTheme.php` | Resolved theme + schema version |
| `src/Theme/ThemeRegistry.php` | Runtime registry (optional `UserTokenSet` merge) |
| `src/Token/ThemeTokenSchema.php` | Required keys for schema `1.0` only |
| `src/Profile/SystemProfile.php` | Structural breakpoints, columns, container widths (default `chameleon-default`) |
| `src/Profile/SystemProfileRegistry.php` | Resolves profile from `symfinity.ui_kernel.system_profile` config |
| `src/Css/CssGenerator.php` | Theme vars + profile z-index/keyframes + layout roles |

Add or change a **built-in** in `config/themes/{lineage}.yaml`: shared **`palette`** (`hues`, `mono`), grouped lineage **`tokens`**, and per-variant **`variants.*`** (`label`, `tone`, nested `colors`, optional `extends`, `scroll_motion`, `backdrop_blur`). Values in `colors` are palette refs only — no raw hex. See `config/themes/README.md`.

## System profile (structural layout)

Appearance tokens stay under theme YAML / **`LayoutProfile`** fallback (**009**). Breakpoints, z-index ladder, global keyframes, and layout roles (`grid`, `stack`, `skeleton`) come from **`SystemProfile`** — normative contract system-profile.

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

Built-in themes target **schema `1.0`** (full colour, layout, overlay, motion, focus-ring tokens). `CssGenerator` rejects unknown schema versions.

### Overlay tokens (**016**)

Resolved per theme (not palette refs) — see native-overlay-css:

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

Disabled under `prefers-reduced-motion: reduce`. Normative: scroll-and-loading-css.

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

Quickstart: 018 palette SSOT quickstart.

## Layout profiles (lineage)

| Profile | Spacing rhythm | Radius | Type base | Motion |
|---------|----------------|--------|-----------|--------|
| **Semantic** | Roomy (`xl` 3rem) | Rounded (md 0.375rem) | Classic 1rem | 200ms |
| **Utility** | Compact mid (`md` 0.75rem) | Subtle (md 0.25rem) | Dense (md 0.875rem) | 150ms |

Built-in **Balanced** themes (`default`, `default-dark`) ship full appearance tokens in YAML and share the Semantic layout profile for rhythm fallback only.

## Balanced (`default`, `default-dark`)

**Lineage:** `default` · **Label:** Balanced / Balanced dark · **Spice:** `cool`

Neutral, modern baseline — white surfaces, sky primary, restrained state colours. Not a third-party stack clone.

| Id | Label | Role |
|----|-------|------|
| `default` | Balanced | Light neutral product baseline |
| `default-dark` | Balanced dark | Dark neutral baseline |

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

Showcase stack gap, button padding, card padding, and form controls read CSS variables — carousel theme change affects **layout** and palette.

## Role CSS (065+)

`CssGenerator` emits **theme tokens and structural profile globals only** — no `[data-ui-role]` component rules. Tier role CSS lives in `ux-blocks-*` packages; see [package-role-css-ownership](../../../../../specs/symfinity/symfinity/3-ux-component-catalog/contracts/package-role-css-ownership.md).

Kernel-themed showcase routes (`/ui-kernel/*`) and feature manifests live in **`symfinity/chameleon-showcase`**, not in this package.

## Fonts

System stacks only in v1. Optional `symfinity/font-manager` pairing: [font-manager-pairing](./font-manager-pairing.md).
