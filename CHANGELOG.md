# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed

- **Mono tones + ramp fit (081)** — Industry mono ids (`slate`, `stone`, `sage`, `mauve`, `rust`, `neutral`); bundle `generator.palette.mono_hues` + lineage `palette.mono_saturation`; `WarmHueRampPolicy` for orange/amber/yellow/lime; dark-tail ease 600→950 (`dark_tail_l_end: 0.09`); parity + CSS snapshots regenerated; 275 PHPUnit green.
- **Computed palette ramps (079)** — `PaletteRampMath` replaces bundle `lightness_curve` / `hue_chroma`; default config is `generator.palette.revision: 1` only. Linear OKLCH L across `contract.palette.levels`; gamut-relative chroma (`chroma_percent` default 100); optional lineage `palette.chroma` overrides. Parity fixtures regenerated; `generator.palette.revision` stays `1`.
- **Mono tone visibility** — `PaletteGenerator::monoSpiceChroma` multiplier raised (`0.04` → `0.24`, lightness floor `0.40`) so tinted neutrals read on surface steps (`mono.100`–`200`); lineage `mono_saturation` drives all tinted tones. `generator.palette.revision` stays `1`.
- **Palette-freeze lift** — built-in lineages (`default`, `semantic`, `utility`) no longer use `palette.anchor_profile` stallion frozen ramps; hue/mono resolve via live OKLCH (`generator.palette.revision` stays `1`). `MaterializedPaletteAnchors` retained as maintainer REF only.

## [0.1.0] - 2026-06-14

Initial baseline of the UI Kernel bundle for Symfony: W3C Design Tokens (DTCG) resolution,
built-in themes on disk, and slim CSS generation. Pre-1.0 and unreleased on Packagist;
`branch-alias` is `dev-main → 0.1.x-dev`.

### Added

- **DTCG token core**
  - Dependency-free `Symfinity\UiKernel\Contract/` SPI (`Token`, `Layer`, `Resolver`, `Exception`)
  - `DtcgDocument`, `DtcgJsonReader`, `DtcgYamlReader`
  - `LayeredTokenResolver` — merges `base ⊕ design_system ⊕ theme`, resolves `{alias}` references with cycle detection
- **Built-in themes**
  - `config/design-systems/{id}.dtcg.yaml` (default `chameleon`)
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
- ui-themer consumer themes (e.g. dogfood zinc YAML) use the bespoke authoring format via `AuthoringThemeConfig` — not `BuiltinDtcgThemeCatalog`.
