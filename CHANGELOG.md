# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.3.1] - 2026-07-05

Patch release after [v0.3.0](https://github.com/symfinity/ui-kernel/releases/tag/v0.3.0). Functional theme inheritance for DTCG variant entries.

### Added

- Theme inheritance — optional `extends` on variant entries in `theme.meta.yaml` merges parent theme-layer DTCG before child overrides (whole-token replace by path)
- Multi-hop chains with cycle detection and configurable `symfinity_ui_kernel.theme_inheritance.max_depth` (default `8`)
- Capability probe ids `nested_theme` and `inheritance_chain` on `ThemeCapabilityProbe` / `ui_kernel_theme_capabilities()`
- Handbook — [themes.md](docs/themes.md) 0.3.1 milestone row and inheritance section; [upgrade.md](docs/upgrade.md) 0.3.1 notes

### Notes

- Theme YAML stays on `schema_version: '2.0'` — flat themes without `extends` are unchanged
- Theme composition author UI remains in ui-themer / ui-designer packages, not this release

## [0.3.0] - 2026-07-05

Minor release after [v0.2.5](https://github.com/symfinity/ui-kernel/releases/tag/v0.2.5). Opens the public **0.3.x theme-engine line**: runtime theme switching, config and session overlays, and a documented capability vocabulary.

### Added

- Runtime theme switching — preference cookies (`symfinity_ui_kernel_lineage`, `symfinity_ui_kernel_scheme`), boot script for pre-paint `data-theme`, JSON scheme endpoint (`PATCH /_ui/theme/scheme`), and `ui_kernel_theme_shell()` for layout chrome with light/dark/auto controls
- Theme capability probe — `ui_kernel_theme_capabilities()` Twig helper and injectable probe service report which theme-engine features are active in the current app (`runtime_switch`, `user_token_overlay`, `session_preview_overlay`, `app_dtcg_override`)
- Handbook — [themes.md](docs/themes.md) (runtime switching, theme capabilities, overlay merge order, 0.3.x milestones), [upgrade.md](docs/upgrade.md) 0.3.0 notes, and [verification.md](docs/verification.md) runtime switching smoke steps
- Public [ROADMAP.md](ROADMAP.md) — **0.3.x** theme engine as the current release line

### Changed

- Preference cookies for unknown lineage or invalid scheme values fall back to configured defaults instead of leaving stale state

### Notes

- Theme YAML stays on `schema_version: '2.0'` — no config migration from 0.2.x beyond confirming `<head>` order: boot script → `ui_kernel_layer_order()` → `ui_kernel_css()`, or `{% include '@UiKernel/_head.html.twig' %}`
- Session preview overlay remains optional — pair with `symfinity/ui-themer` on designated preview hosts; runtime switching does not require ui-themer
- Nested themes and a multi-hop inheritance resolver are planned for later **0.3.x** patches, not this release

## [0.2.5] - 2026-07-05

Patch release after [v0.2.4](https://github.com/symfinity/ui-kernel/releases/tag/v0.2.4). Public roadmap, sponsorship links, and handbook copy polish — no theme runtime or API changes.

### Added

- **Public roadmap** — [ROADMAP.md](ROADMAP.md) with 0.1.x–1.0.x milestone table (current line: 0.2.x DTCG and colour system)
- **Sponsors** — [SUPPORTERS.md](SUPPORTERS.md) and GitHub Sponsors metadata in `composer.json`

### Changed

- **Handbook** — [configuration.md](docs/configuration.md), [themes.md](docs/themes.md), and [reference.md](docs/reference.md) use consumer wording only (no internal planning references)
- **[Font Manager pairing](docs/font-manager-pairing.md)** — documents `symfinity/font-manager` `ui_kernel_bridge` and `font_manager_ui_kernel_bridge()`; layout example includes boot script and cascade layer order

### Notes

- No changes to `schema_version`, semantic colour slugs, physics axis, DTCG theme files, or generated CSS since v0.2.4
- `composer update symfinity/ui-kernel` is sufficient; no config migrations
- Pair with `symfinity/font-manager` `^0.2.6` when using the optional font bridge (ui-kernel itself has no new PHP API in this release)

## [0.2.4] - 2026-07-05

Patch release after [v0.2.3](https://github.com/symfinity/ui-kernel/releases/tag/v0.2.3). CSS cascade layers for the Chameleon platform stack and handbook alignment for font-manager pairing.

### Added

- **CSS cascade layers** — platform layer order (`kernel.tokens`, `kernel.profile`, `blocks.`*, `app`); generated theme CSS wrapped in `@layer kernel.tokens` and `@layer kernel.profile`
- `ui_kernel_layer_order()` — Twig helper that emits the `@layer` declaration in `<head>` (call before `ui_kernel_css()`)
- **Handbook** — `docs/verification.md` (local smoke commands for themed layouts)

### Changed

- `@UiKernel/_head.html.twig` — includes `ui_kernel_layer_order()` between boot script and theme CSS
- `docs/quickstart.md` — documents cascade layer order in the layout `<head>` sequence
- `docs/font-manager-pairing.md` — seamless pairing via font-manager `ui_kernel_bridge` and `font_manager_ui_kernel_bridge()`; documents correct `font_manager()` parameter order
- `docs/index.md` — handbook page list updated

### Notes

- Custom layouts that call `ui_kernel_css()` directly should add `{{ ui_kernel_layer_order()|raw }}` before theme CSS, or switch to `{% include '@UiKernel/_head.html.twig' %}`
- Unlayered integrator CSS continues to override layered `ux-blocks-*` rules at equal specificity — pair with ux-blocks tier releases that emit matching `blocks.*` layers when upgrading component CSS
- No changes to `schema_version`, semantic colour slugs, or DTCG theme files since v0.2.3
- `composer update symfinity/ui-kernel` is sufficient; no config migrations

## [0.2.3] - 2026-06-29

Patch release after [v0.2.2](https://github.com/symfinity/ui-kernel/releases/tag/v0.2.2). Split mirror CI maintenance — no PHP, theme-runtime, or handbook changes.

### Changed

- **Split mirror CI** — Composer package cache and `GITHUB_TOKEN` authentication so GitHub Actions reliably resolves dependencies across the PHP × Symfony matrix

### Notes

- No changes to `schema_version`, semantic colour slugs, physics axis, or generated CSS since v0.2.2
- `composer update symfinity/ui-kernel` is sufficient; no config or theme file migrations

## [0.2.2] - 2026-06-29

Patch release after [v0.2.1](https://github.com/symfinity/ui-kernel/releases/tag/v0.2.1). Consumer handbook polish for the split mirror — no PHP or theme-runtime changes.

### Added

- **Handbook** — `docs/commands.md` (kernel has no standalone console commands; related tooling pointers)
- **Handbook** — `docs/reference.md` (config root, DTCG layout, key PHP entry points, related packages)
- **Handbook** — `docs/troubleshooting.md` (stale theme CSS, upgrade mismatches, Web Profiler panel)

### Changed

- `docs/index.md` — lists all non-stub handbook pages including the new reference and troubleshooting guides
- `docs/architecture.md`, `docs/themes.md`, `docs/configuration.md`, `docs/usage.md` — consumer-facing copy; authoring-theme boundary clarified without maintainer-only package paths
- `docs/profiler.md` — baseline Web Profiler collector only (removed ui-profiler layout note)
- `docs/dtcg-globals-consumers.md` — generic variant-catalog consumer wording
- `docs/installation.md` — trimmed to omnia-shaped consumer install (no compile frontmatter)

### Removed

- `docs.yaml` — internal handbook compile manifest (split mirror ships `docs/` directly)

### Notes

- No changes to `schema_version`, semantic colour slugs, physics axis, or generated CSS since v0.2.1
- `composer update symfinity/ui-kernel` is sufficient; no config or theme file migrations

## [0.2.1] - 2026-06-25

Patch release after [v0.2.0](https://github.com/symfinity/ui-kernel/releases/tag/v0.2.0). Built-in theme file rename and documentation alignment — no new semantic-colour or physics-axis behaviour.

### Changed

- **Built-in default theme DTCG file** — `config/themes/default/chameleon.dtcg.yaml` renamed to `symfinity.dtcg.yaml`; theme meta files and handbook updated
- `DesignSystemLayerRegistry` — default design system id aligned to Symfinity naming
- `ThemeTokenSchema`, `ColourPropsNormalizer`, and `SemanticColourVocabulary` — minor token/schema alignment fixes
- **Handbook** — `docs/architecture.md`, `docs/profiler.md`, and `docs/themes.md` reference the renamed built-in theme file
- **Test fixtures** — DTCG catalog and `export-dtcg-themes` script updated for the new filename

### Notes

- Custom app themes are unaffected unless you copied the old `chameleon.dtcg.yaml` filename from the bundle — prefer `symfinity.dtcg.yaml` for new overrides
- No changes to `schema_version`, semantic colour slugs, or physics axis since v0.2.0

## [0.2.0] - 2026-06-22

Minor release after [v0.1.2](https://github.com/symfinity/ui-kernel/releases/tag/v0.1.2). Semantic colour vocabulary v2, physics axis, and compound elevation shadows. **Breaking** for custom DTCG themes and snapshots that still use `tertiary` / `ghost` semantic colours.

### Added

- **Semantic colour vocabulary v2** — eight canonical slugs for `data-ui-variant`: `primary`, `secondary`, `accent`, `success`, `warning`, `danger`, `info`, `neutral`
- `color.neutral` / `--ui-color-neutral` — achromatic chrome semantic (distinct from mono tone `neutral` ramp steps)
- `schema_version: '2.0'` — app config key; default in bundle and Flex-copied config
- `ColourPropsNormalizer` — legacy prop aliases at normalize time: `tertiary` → `accent`, `ghost` → `neutral`, `destructive` → `danger`, empty/`default` → `primary`
- **Physics axis** — material profiles `flat`, `glass`, and `retro` via `default_physics` and `data-ui-physics` on `[data-ui-root]` or `html`
- `PhysicsCssEmitter` — `[data-ui-physics="…"]` blocks with `--ui-physics-*` tokens and bridges to `--ui-motion-*` / `--ui-radius-*`
- `EffectivePhysicsResolver` — `glass` on a light variant coerces to `flat` with a PSR-3 warning
- `CompoundShadowBuilder` — compound inset highlight + drop shadows on `--ui-shadow-sm|md|lg` at theme resolve time (lineage × mode × surface)
- **Handbook** — physics axis and semantic-colour homonyms in `docs/themes.md`; `schema_version` and `default_physics` in `docs/configuration.md`

### Changed

- **Built-in DTCG themes** — `color.tertiary` renamed to `color.accent` on all six bundle variants
- `ThemeDtcgResolver` / `ThemeTokenResolver` / `PresetRegistry` — flat `rgba` shadow literals replaced with compound `color-mix` strings; `--ui-overlay-shadow` aliases the strongest shadow tier
- **Generated CSS** — `--ui-color-accent` and `--ui-color-neutral` emitted; `--ui-color-tertiary` no longer present
- **CSS parity fixtures** — regenerated for all six built-in theme variants

### Removed

- `color.tertiary` / `--ui-color-tertiary` — use `accent`
- `color.ghost` / `--ui-color-ghost` — transparent controls use `variant="neutral"` with `appearance="ghost"` on Button/Link in `symfinity/ux-blocks-core` (pair with `^0.2`)

### Notes

- Upgrade custom theme YAML: set `schema_version: '2.0'`, rename `color.tertiary` → `color.accent`, drop `color.ghost`, add `color.neutral` where needed — see [docs/upgrade.md](docs/upgrade.md)
- Re-check snapshots and visual baselines that assert exact `--ui-color-*` or shadow strings
- Flex recipe `0.1` constraint unchanged (`^0.1`); new installs receive `schema_version` and `default_physics` defaults when the copied app config is refreshed
- Pair with `symfinity/ux-blocks-core` `^0.2` for the five-value `appearance` axis (`solid`, `soft`, `outline`, `ghost`, `link`)

## [0.1.2] - 2026-06-19

Patch release after [v0.1.1](https://github.com/symfinity/ui-kernel/releases/tag/v0.1.1). Adds optional consumer theme overrides and a solid-button label token; yellow ramp values may differ from v0.1.1.

### Added

- **Consumer app DTCG themes** — ship lineages under `config/themes/{lineage}/` (same layout as bundle); app lineages override bundle lineages with the same folder name; invalid app lineages are skipped with a PSR-3 warning
- `themes_directory` — app config key defaulting to `%kernel.project_dir%/config/themes`
- `ThemeRegistry::has()` — check whether a theme id is registered
- `--ui-color-button-text` — semantic CSS variable for on-fill label colour on solid controls (light and dark schemes)
- **Built-in DTCG `color.button.text`** — on all six bundle theme variants
- **Handbook** — consumer app overrides in `docs/themes.md`

### Changed

- `BuiltinDtcgThemeCatalog` — merges bundle and app theme directories; shared catalog instance wired through `ThemeRegistry` and `ThemeCatalog`
- `BuiltinThemeVariant` — exposes `mode()` and `catalogSource()` (`kernel` or `app`)
- **Yellow perceptual midtone ramp** — adjusted L for levels 200–500; default lineage yellow hue 95° → 100°
- **Generated CSS** — includes `--ui-color-button-text`; yellow `--ui-color-`* steps may differ from v0.1.1

### Removed

- `config/reference.php` — auto-generated Symfony config reference (apps-only; not part of the bundle API)

### Notes

- No PHP public API removals — patch semver
- Flex recipe `0.1` unchanged in constraint; default copied app config includes `themes_directory`
- Apps without a `config/themes/` tree behave as on v0.1.1

## [0.1.1] - 2026-06-16

Palette ramp generation overhaul since [v0.1.0](https://github.com/symfinity/ui-kernel/releases/tag/v0.1.0) — same `generator.palette.revision: 1`, replaced math in place.

### Added

- `PerceptualMidtoneRampPolicy` — hybrid midtone correction (gamut headroom × hue archetype); levels 50–500 envelope peaks at 500; narrow-warm L overrides for amber, yellow, lime
- `HueArchetypeRegistry` — per-hue-family strength multipliers for midtone policy
- **Bundle mono centralization** — `contract.palette.mono_tones` + `generator.palette.mono_hues` (slate, stone, sage, mauve, rust); lineage `palette.mono_saturation` only (no per-lineage hue drift)
- **Handbook** — `docs/upgrade.md` (path-repo → Flex, version bumps, visual baseline note)

### Changed

- `PaletteRampMath` — linear OKLCH lightness by level index; gamut-relative chroma with `levelChromaScale` floor; dark-tail curve updated in place (quadratic steps 600–950; **950** = **L(900)/2**); `generator.palette.revision` stays **1**
- `PaletteGenerator` — 500→600 bridge **ΔL = 0.070**; midtone pipeline uses `PerceptualMidtoneRampPolicy` instead of warm-hue taper
- **Built-in theme metadata** — industry mono tone ids; slate hue normalized to **240°** at bundle SSOT
- **Generated CSS** — `--ui-color-`* values for hue and tinted-mono ramps differ from `v0.1.0` at the same YAML keys (refresh visual baselines after upgrade)

### Removed

- `WarmHueRampPolicy` — superseded by perceptual midtone pipeline (in-place at `generator.palette.revision: 1`)

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

