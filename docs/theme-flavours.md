# Theme flavours (package)

Normative contracts: [theme-flavours](../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/theme-flavours.md), [baseline-flavours](../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/baseline-flavours.md), [theme-token-engine](../../../../specs/symfinity/symfinity/9-ui-kernel-theme-tokens/contracts/theme-token-engine.md).

## SSOT (code)

| File | Role |
|------|------|
| `src/Token/FlavourThemeConfig.php` | Built-in flavour ids, labels, lineage, spice, **palette refs** (no hex) |
| `src/Token/PaletteAnchors.php` | Showcase parity hex anchors (internal palette SSOT) |
| `src/Token/PaletteGenerator.php` | mono+spice ramps, hue families, alpha modifier |
| `src/Token/ThemeTokenResolver.php` | Merge lineage presets + semantic colours → `DesignTokenSet` |
| `src/Token/LineagePresetRegistry.php` | Kiroshi / Semantic / Utility — space, radius, type, shadow, motion |
| `src/Flavour/FlavourCatalog.php` | Loads configs through resolver → `DefinedFlavour` |
| `src/Flavour/LayoutProfile.php` | Lineage enum; delegates layout tokens to registry |
| `src/Flavour/DefinedFlavour.php` | Resolved flavour + schema version |
| `src/Flavour/FlavourRegistry.php` | Runtime registry (optional `UserTokenSet` merge) |
| `src/Token/ThemeTokenSchema.php` | Required keys for schema `1.0` / `2.0` (incl. `--ui-overlay-*`, `--ui-backdrop-*` at **2.0**) |
| `src/Profile/SystemProfile.php` | Structural breakpoints, columns, container widths (default `chameleon-default`) |
| `src/Profile/SystemProfileRegistry.php` | Resolves profile from `symfinity.ui_kernel.system_profile` config |
| `src/Css/CssGenerator.php` | Theme vars + profile z-index/keyframes + layout roles at schema `2.0` |

Add or change a flavour in **`FlavourThemeConfig` only** — use palette refs, not raw hex. Hex lives in `PaletteAnchors` / generator only.

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

Built-in flavours target **schema `2.0`** (tertiary, warning, info, focus, overlay, skeleton, shadows, motion, focus-ring tokens). `CssGenerator` accepts `schemaVersion` for compatibility snapshots.

### Overlay tokens (**016**)

Resolved per flavour (not palette refs) — see [native-overlay-css](../../../../specs/symfinity/symfinity/16-ui-kernel-final-css/contracts/native-overlay-css.md):

| Token | Source |
|-------|--------|
| `--ui-overlay-surface` | `--ui-color-surface-elevated` |
| `--ui-overlay-border` | `--ui-color-border` |
| `--ui-overlay-shadow` | `--ui-shadow-lg` |
| `--ui-backdrop-color` | `--ui-color-overlay` |
| `--ui-backdrop-blur` | Flavour default (`0`; `6px` on semantic pair for marketing) |

Z-index for modals/popovers uses profile `--ui-z-*` only — never literals in flavour PHP.

### Scroll motion flag

| Flavour | `scrollMotion` |
|---------|----------------|
| `semantic`, `semantic-dark` | `true` — emits `[data-ui-scroll-reveal]` scroll-timeline rules |
| All others | `false` |

Disabled under `prefers-reduced-motion: reduce`. Normative: [scroll-and-loading-css](../../../../specs/symfinity/symfinity/16-ui-kernel-final-css/contracts/scroll-and-loading-css.md).

## User overrides

Partial overrides via Symfony config (`symfinity_ui_kernel.user_tokens`) — see `config/packages/symfinity_ui_kernel.yaml`. Unknown keys rejected at merge time.

## Layout profiles (lineage)

| Profile | Spacing rhythm | Radius | Type base | Motion |
|---------|----------------|--------|-----------|--------|
| **Kiroshi** | Tight (`md` 0.625rem) | Sharp (0) | Slightly compact (0.9375rem) | 150ms |
| **Semantic** | Roomy (`xl` 3rem) | Rounded (md 0.375rem) | Classic 1rem | 200ms |
| **Utility** | Compact mid (`md` 0.75rem) | Subtle (md 0.25rem) | Dense (md 0.875rem) | 150ms |

Showcase stack gap, button padding, card padding, and form controls read CSS variables — carousel flavour change affects **layout** and palette.

## Kiroshi (`default`, `dark`)

**Lineage:** `LayoutProfile::Kiroshi` · **Spice:** `warm`

**Visual inspiration:** Night City palette cues from the public [Cyberpunk 2077](https://www.cyberpunk.net/) site. Symfinity **inspired-by** styling only.

| Id | Label | Surface (resolved) |
|----|-------|---------------------|
| `default` | Kiroshi | Neon yellow field |
| `dark` | Kiroshi dark | Near-black field |

## Semantic & Utility stacks

| Ids | Layout profile | Spice |
|-----|----------------|-------|
| `semantic`, `semantic-dark` | `LayoutProfile::Semantic` | `cool` |
| `utility`, `utility-dark` | `LayoutProfile::Utility` | `cool` |

## Fonts

System stacks only in v1. Optional `symfinity/font-manager` pairing: [font-manager-pairing](./font-manager-pairing.md).
