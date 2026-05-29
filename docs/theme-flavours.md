# Theme flavours (package)

Normative contracts: [theme-flavours](../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/theme-flavours.md), [baseline-flavours](../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/baseline-flavours.md).

## SSOT (code)

| File | Role |
|------|------|
| `src/Flavour/FlavourCatalog.php` | All flavour ids, labels, **color** values |
| `src/Flavour/LayoutProfile.php` | Shared **layout** tokens per lineage (Kiroshi, Semantic, Utility) |
| `src/Flavour/DefinedFlavour.php` | Merges layout + colors → `DesignTokenSet` |
| `src/Flavour/FlavourRegistry.php` | Runtime registry (loads catalog) |
| `src/Token/ThemeTokenSchema.php` | Required key names (`COLOR_KEYS`, `LAYOUT_KEYS`) |

Add or change a flavour in **`FlavourCatalog` only** — do not duplicate layout scales in color maps.

Light/dark pairs in the same lineage share one `LayoutProfile`; only **colors** differ between e.g. `semantic` and `semantic-dark`.

## Layout profiles (lineage)

| Profile | Spacing rhythm | Radius | Type base | Motion |
|---------|----------------|--------|-----------|--------|
| **Kiroshi** | Tight (`md` 0.625rem) | Sharp (0) | Slightly compact (0.9375rem) | 150ms |
| **Semantic** | Roomy (`xl` 3rem) | Rounded (md 0.375rem) | Classic 1rem | 200ms |
| **Utility** | Compact mid (`md` 0.75rem) | Subtle (md 0.25rem) | Dense (md 0.875rem) | 150ms |

Showcase stack gap, button padding, card padding, and form controls all read these CSS variables — switching carousel flavour should change **layout** as well as palette.

## Kiroshi (`default`, `dark`)

**Lineage:** `LayoutProfile::Kiroshi`.

**Visual inspiration:** Night City palette cues from the public [Cyberpunk 2077](https://www.cyberpunk.net/) site. Symfinity **inspired-by** styling only.

| Id | Label | Surface |
|----|-------|---------|
| `default` | Kiroshi | Neon yellow `#fcee0a` page field |
| `dark` | Kiroshi dark | Near-black `#050508` |

## Semantic & Utility stacks

| Ids | Layout profile |
|-----|----------------|
| `semantic`, `semantic-dark` | `LayoutProfile::Semantic` |
| `utility`, `utility-dark` | `LayoutProfile::Utility` |
