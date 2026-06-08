# Changelog

All changes to **symfinity/ui-kernel** are currently on `main` (unreleased, untagged).

## [Unreleased]

### Changed

- **Palette generator v2 (OKLCH)** — `generator.palette.interpolation: oklch`, `revision: 2`. HSL path removed. Resolved sRGB for unchanged ref strings may drift; theme-token schema **`1.0`** unchanged. Normative: [oklch-palette-generator](../../specs/symfinity/symfinity/2-ui-kernel/contracts/oklch-palette-generator.md) (**059**).
- Theme token schema **`1.0` only** — full token set; `ThemeTokenSchema` rejects other versions. Normative: [theme-token-schema](../../specs/symfinity/symfinity/2-ui-kernel/contracts/theme-token-schema.md), [built-in-theme-yaml](../../specs/symfinity/symfinity/2-ui-kernel/contracts/built-in-theme-yaml.md).
- Bundle palette SSOT: `contract.palette` / `generator.palette` in `config/packages/symfinity_ui_kernel.yaml`. Built-in themes: `symfinity_ui_kernel.themes.{lineage}` in `config/themes/*.yaml` — grouped `tokens`, nested `colors`, variant `extends`.
- Four shipped theme files; removed `config/palette_ssot.yaml`. Baseline lineage `default.yaml`; public ids `default-dark`, `semantic-dark`, …
- Chameleon vocabulary — **Flavour** → **Theme**; config `default_theme`; authoring `preset` / `tone`. Spec: [theme-vocabulary](../../specs/symfinity/symfinity/2-ui-kernel/contracts/theme-vocabulary.md) (**031**).

### Added

- **`PaletteRampSampler`** public port — enumerate grammar-valid refs with OKLCH tuples for import consumers (**055**). See [oklch-palette-generator](../../specs/symfinity/symfinity/2-ui-kernel/contracts/oklch-palette-generator.md).
- Dev-only Web Profiler `UiKernelDataCollector` — WDT palette badge + profiler panel for theme resolution and CSS metrics ([profiler.md](docs/profiler.md))
- Symfony bundle `UiKernelBundle` for integration
- Modules: `Component`, `Css`, `Page`, `Palette`, `Profile`, `Renderer`, `Theme`
- Package configuration under `config/`; Twig templates for UI integration
