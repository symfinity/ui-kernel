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
| `src/Token/ThemeTokenSchema.php` | Required keys for schema `1.0` / `2.0` |

Add or change a flavour in **`FlavourThemeConfig` only** — use palette refs, not raw hex. Hex lives in `PaletteAnchors` / generator only.

Light/dark pairs share one `LayoutProfile`; **semantic colour refs** differ between e.g. `semantic` and `semantic-dark`.

## Schema

Built-in flavours target **schema `2.0`** (tertiary, warning, info, focus, overlay, skeleton, shadows, motion, focus-ring tokens). `CssGenerator` accepts `schemaVersion` for compatibility snapshots.

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
