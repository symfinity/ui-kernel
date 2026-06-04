# Changelog

All changes to **symfinity/ui-kernel** are currently on `main` (unreleased, untagged).

## [Unreleased]

### Changed

- Theme token schema **`1.0` only** — full token set; `ThemeTokenSchema` rejects other versions. Normative: [theme-token-schema](../../specs/symfinity/symfinity/2-ui-kernel/contracts/theme-token-schema.md), [built-in-theme-yaml](../../specs/symfinity/symfinity/2-ui-kernel/contracts/built-in-theme-yaml.md).
- Bundle palette SSOT: `contract.palette` / `generator.palette` in `config/packages/symfinity_ui_kernel.yaml`. Built-in themes: `symfinity_ui_kernel.themes.{lineage}` in `config/themes/*.yaml` — grouped `tokens`, nested `colors`, variant `extends`.
- Four shipped theme files; removed `config/palette_ssot.yaml`. Baseline lineage `default.yaml`; public ids `default-dark`, `semantic-dark`, …
- Chameleon vocabulary — **Flavour** → **Theme**; config `default_theme`; authoring `preset` / `tone`. Spec: [theme-vocabulary](../../specs/symfinity/symfinity/2-ui-kernel/contracts/theme-vocabulary.md) (**031**).

### Added

- Symfony bundle `UiKernelBundle` for integration
- Modules: `Component`, `Css`, `Page`, `Palette`, `Profile`, `Renderer`, `Theme`
- Package configuration under `config/`; Twig templates for UI integration
