# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.1] - 2026-06-16

### Changed

- Flex recipe pins `symfinity/ui-kernel: ^0.1.1`
- Handbook: `docs/upgrade.md` with upgrade path from private path-repo installs

### Notes

- No breaking API changes — patch release following `v0.1.0`

## [0.1.0] - 2026-06-15

Initial public release of the UI Kernel bundle for Symfony: W3C Design Tokens (DTCG) resolution,
built-in themes on disk, and slim CSS generation.

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
