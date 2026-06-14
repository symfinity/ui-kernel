# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.2] - 2026-06-14

### Added

- Composer `suggest` for `symfinity/ux-blocks-demo` — browser theme galleries and kernel showcase routes (dev/test only)

### Changed

- Minimum PHP restored to **8.2+** in `composer.json` (see `v0.1.1` note below)
- Consumer handbook — version upgrade paths, overlay token documentation, optional demo install

### Fixed

- Light mode text contrast — built-in themes use `mono.*.900` / `mono.*.500` for default and muted copy; generated theme CSS sets `color: var(--ui-color-text)` on `:root` / `[data-theme]`
- Palette catalog and theme YAML normalisation hardening for type-safe token resolution

## [0.1.1] - 2026-06-14

### Fixed

- Theme-tone alignment for mono-based semantic colour refs — warm/cool/pure families stay consistent within a lineage (`SemanticColorMap::applyThemeTone`)

### Changed

- Minimum PHP in `composer.json` lowered to `>=8.1` (unintended; restored in `v0.1.2`)

## [0.1.0] - 2026-06-13

### Added

- Initial release of UI Kernel bundle for Symfony
- Design tokens and generated theme CSS (`--ui-color-*`, spacing, radius, motion, focus)
- Overlay design tokens (`--ui-overlay-surface`, `--ui-overlay-border`, `--ui-overlay-shadow`, `--ui-backdrop-color`, `--ui-backdrop-blur`) derived from the active theme
- Built-in theme lineages: Balanced (`default`), Semantic, and Utility (light + dark variants)
- OKLCH palette generator (revision `1`) with theme token schema `1.0`
- Symfony Flex recipe `0.1` — bundle registered for all environments; default app config copied from package
- App configuration: `default_theme`, `default_variant`, `user_tokens`, `system_profile`
- Application config guard — non-empty `contract` or `generator` keys in app YAML fail at compile time (bundle SSOT only)
- Twig helpers:
  - `ui_kernel_css()`
  - `ui_kernel_theme_boot_script()`
  - `ui_kernel_active_theme_id()`
  - `ui_kernel_theme_shell()`
- `@UiKernel/_head.html.twig` partial for document head integration
- Optional dev Web Profiler data collector when `symfony/web-profiler-bundle` is installed
- Consumer handbook under `docs/`
- Symfony 6.4, 7.x, and 8.x compatibility (PHP 8.2+)
- Split mirror CI: PHPStan 2.x with `phpstan-symfony` on the maintained PHP × Symfony matrix

### Notes

- UI Kernel emits theme tokens and structural profile globals only — component `[data-ui-role]` CSS ships in separate `symfinity/ux-blocks-*` packages.
- Split mirror CI: PHP 8.2–8.5 × Symfony 6.4 / 7.4 / 8.0 / 8.1 (see `.github/workflows/ci.yml`).
