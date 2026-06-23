# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.2.0] - 2026-06-22

Minor release after [v0.1.2](https://github.com/symfinity/ui-kernel/releases/tag/v0.1.2). Semantic colour vocabulary v2, physics axis, and compound elevation shadows. **Breaking** for custom DTCG themes and snapshots that still use `tertiary` / `ghost` semantic colours.

### Added

- **Semantic colour vocabulary v2** — eight canonical slugs for `data-ui-variant`: `primary`, `secondary`, `accent`, `success`, `warning`, `danger`, `info`, `neutral`
- **`color.neutral` / `--ui-color-neutral`** — achromatic chrome semantic (distinct from mono tone `neutral` ramp steps)
- **`schema_version: '2.0'`** — app config key; default in bundle and Flex-copied config
- **`ColourPropsNormalizer`** — legacy prop aliases at normalize time: `tertiary` → `accent`, `ghost` → `neutral`, `destructive` → `danger`, empty/`default` → `primary`
- **Physics axis** — material profiles `flat`, `glass`, and `retro` via `default_physics` and `data-ui-physics` on `[data-ui-root]` or `html`
- **`PhysicsCssEmitter`** — `[data-ui-physics="…"]` blocks with `--ui-physics-*` tokens and bridges to `--ui-motion-*` / `--ui-radius-*`
- **`EffectivePhysicsResolver`** — `glass` on a light variant coerces to `flat` with a PSR-3 warning
- **`CompoundShadowBuilder`** — compound inset highlight + drop shadows on `--ui-shadow-sm|md|lg` at theme resolve time (lineage × mode × surface)
- **Handbook** — physics axis and semantic-colour homonyms in `docs/themes.md`; `schema_version` and `default_physics` in `docs/configuration.md`

### Changed

- **Built-in DTCG themes** — `color.tertiary` renamed to `color.accent` on all six bundle variants
- **`ThemeDtcgResolver` / `ThemeTokenResolver` / `PresetRegistry`** — flat `rgba` shadow literals replaced with compound `color-mix` strings; `--ui-overlay-shadow` aliases the strongest shadow tier
- **Generated CSS** — `--ui-color-accent` and `--ui-color-neutral` emitted; `--ui-color-tertiary` no longer present
- **CSS parity fixtures** — regenerated for all six built-in theme variants

### Removed

- **`color.tertiary` / `--ui-color-tertiary`** — use `accent`
- **`color.ghost` / `--ui-color-ghost`** — transparent controls use `variant="neutral"` with `appearance="ghost"` on Button/Link in `symfinity/ux-blocks-core` (pair with `^0.2`)

### Notes

- Upgrade custom theme YAML: set `schema_version: '2.0'`, rename `color.tertiary` → `color.accent`, drop `color.ghost`, add `color.neutral` where needed — see [docs/upgrade.md](docs/upgrade.md)
- Re-check snapshots and visual baselines that assert exact `--ui-color-*` or shadow strings
- Flex recipe `0.1` constraint unchanged (`^0.1`); new installs receive `schema_version` and `default_physics` defaults when the copied app config is refreshed
- Pair with **`symfinity/ux-blocks-core` `^0.2`** for the five-value `appearance` axis (`solid`, `soft`, `outline`, `ghost`, `link`)

## [0.1.2] - 2026-06-19

Patch release after [v0.1.1](https://github.com/symfinity/ui-kernel/releases/tag/v0.1.1). Adds optional consumer theme overrides and a solid-button label token; yellow ramp values may differ from v0.1.1.

### Added

- **Consumer app DTCG themes** — ship lineages under `config/themes/{lineage}/` (same layout as bundle); app lineages override bundle lineages with the same folder name; invalid app lineages are skipped with a PSR-3 warning
- **`themes_directory`** — app config key defaulting to `%kernel.project_dir%/config/themes`
- **`ThemeRegistry::has()`** — check whether a theme id is registered
- **`--ui-color-button-text`** — semantic CSS variable for on-fill label colour on solid controls (light and dark schemes)
- **Built-in DTCG `color.button.text`** — on all six bundle theme variants
- **Handbook** — consumer app overrides in `docs/themes.md`

### Changed

- **`BuiltinDtcgThemeCatalog`** — merges bundle and app theme directories; shared catalog instance wired through `ThemeRegistry` and `ThemeCatalog`
- **`BuiltinThemeVariant`** — exposes `mode()` and `catalogSource()` (`kernel` or `app`)
- **Yellow perceptual midtone ramp** — adjusted L for levels 200–500; default lineage yellow hue 95° → 100°
- **Generated CSS** — includes `--ui-color-button-text`; yellow `--ui-color-*` steps may differ from v0.1.1

### Removed

- **`config/reference.php`** — auto-generated Symfony config reference (apps-only; not part of the bundle API)

### Notes

- No PHP public API removals — patch semver
- Flex recipe `0.1` unchanged in constraint; default copied app config includes `themes_directory`
- Apps without a `config/themes/` tree behave as on v0.1.1

## [0.1.1] - 2026-06-16

Palette ramp generation overhaul since [v0.1.0](https://github.com/symfinity/ui-kernel/releases/tag/v0.1.0) — same `generator.palette.revision: 1`, replaced math in place.

### Added

- **`PerceptualMidtoneRampPolicy`** — hybrid midtone correction (gamut headroom × hue archetype); levels 50–500 envelope peaks at 500; narrow-warm L overrides for amber, yellow, lime
- **`HueArchetypeRegistry`** — per-hue-family strength multipliers for midtone policy
- **Bundle mono centralization** — `contract.palette.mono_tones` + `generator.palette.mono_hues` (slate, stone, sage, mauve, rust); lineage `palette.mono_saturation` only (no per-lineage hue drift)
- **Handbook** — `docs/upgrade.md` (path-repo → Flex, version bumps, visual baseline note)

### Changed

- **`PaletteRampMath`** — linear OKLCH lightness by level index; gamut-relative chroma with `levelChromaScale` floor; dark-tail curve updated in place (quadratic steps 600–950; **950** = **L(900)/2**); `generator.palette.revision` stays **1**
- **`PaletteGenerator`** — 500→600 bridge **ΔL = 0.070**; midtone pipeline uses `PerceptualMidtoneRampPolicy` instead of warm-hue taper
- **Built-in theme metadata** — industry mono tone ids; slate hue normalized to **240°** at bundle SSOT
- **Generated CSS** — `--ui-color-*` values for hue and tinted-mono ramps differ from `v0.1.0` at the same YAML keys (refresh visual baselines after upgrade)

### Removed

- **`WarmHueRampPolicy`** — superseded by perceptual midtone pipeline (in-place at `generator.palette.revision: 1`)

### Notes

- No PHP public API removals — patch semver; **visual/token output is not byte-identical to v0.1.0**
- Flex recipe `0.1` unchanged in behaviour (bundle registration + `copy-from-package` config only)

## [0.1.0] - 2026-06-15

Initial public release of the UI Kernel bundle for Symfony: W3C Design Tokens (DTCG) resolution,
built-in themes on disk, and slim CSS generation.

### Added

- **DTCG token core**
  - Dependency-free `Symfinity\UiKernel\Contract/` SPI (`Token`, `Layer`, `Resolver`, `Exception`)
  - `DtcgDocument`, `DtcgJsonReader`, `DtcgYamlReader`
  - `LayeredTokenResolver` — merges `base ⊕ design_system ⊕ theme`, resolves `{alias}` references with cycle detection
- **Built-in themes**
  - `config/design-systems/{id}.dtcg.yaml` (default `symfinity`)
  - `config/themes/{lineage}/theme.meta.yaml` + per-variant `.dtcg.yaml` files
  - Balanced (`default`), Semantic, and Utility lineages (light + dark variants)
  - `BuiltinDtcgThemeCatalog`, `DesignSystemLayerRegistry`, `LayerStackBuilder`, `ThemeDtcgResolver`
  - `Theme::designSystemId()` from `theme.meta.yaml`
- **CSS generation**
  - `CssVariableSet` and `CssGenerator::forResolvedGraph()` / `forTheme()` / `forAdaptiveThemePair()`
  - `--ui-color-*`, spacing, radius, motion, focus, and overlay tokens from the active graph
  - Cache keys fold `ResolvedGraph::layerSignature()` and profile-globals revision
  - `config/tokens/profile-globals.dtcg.{yaml,json}` — z-index ladder and global `@keyframes`
  - `ProfileGlobalsLayerRegistry`, `AtRulesContributorInterface`, `AtRulesContributor`
- **Semantic colour vocabulary**
  - `SemanticColourVocabulary`, `ColourPropsNormalizer`, `GraphVariantReader` — graph-derived names for `data-ui-variant`
  - `GraphVariantCatalogPort` and `GraphVariantCatalog` for read-only slug lists (workshop, profiler)
- **OKLCH palette generator** (revision `1`) — native DTCG base-layer emission with schema `1.0`
  - Computed ramps: linear OKLCH lightness across palette levels; gamut-relative chroma
  - Industry mono tone ids (`slate`, `stone`, `sage`, `mauve`, `rust`, `neutral`) with per-lineage saturation
  - Warm-hue ramp taper for midtones; improved tinted-neutral visibility on surface steps
  - Live OKLCH for built-in lineages (no frozen anchor-profile hex tables)
- **Consumer theme authoring** — `AuthoringThemeConfig` + `ThemeTokenResolver` for ui-themer / bespoke YAML (not built-in catalog)
- **Composition preview** (ui-themer integration)
  - `SessionThemeInjectionPort`, `PreviewHostContext`, `InjectedThemeCssProvider` in `Preview/`
  - Transient draft CSS on preview hosts without `ThemeRegistry` mutation; implemented by `symfinity/ui-themer`
- **Symfony integration**
  - Flex recipe `0.1` — bundle registered for all environments; default app config copied from package
  - App config: `default_theme`, `default_variant`, `user_tokens`, `system_profile`
  - Compile-time guard — non-empty `contract` or `generator` keys in app YAML are rejected (bundle SSOT only)
  - Twig: `ui_kernel_css()`, `ui_kernel_theme_boot_script()`, `ui_kernel_active_theme_id()`, `ui_kernel_theme_shell()`
  - `@UiKernel/_head.html.twig` partial
  - Optional Web Profiler data collector when `symfony/web-profiler-bundle` is installed
- **Docs** — consumer handbook under `docs/` (DTCG token core, themes, installation, quick start)
- **Compatibility** — PHP 8.2+; Symfony 6.4, 7.x, and 8.x
- **CI** — split mirror PHPStan 2.x with `phpstan-symfony` on the maintained PHP × Symfony matrix

### Notes

- UI Kernel emits theme tokens and structural profile globals only; component `[data-ui-role]` CSS ships in separate `symfinity/ux-blocks-*` packages.
- ui-themer consumer themes use the bespoke authoring format via `AuthoringThemeConfig` — not `BuiltinDtcgThemeCatalog`.
