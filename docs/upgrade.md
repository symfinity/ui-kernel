# Upgrade guide

## First public release (`v0.1.0`)

This is the first tagged release on Packagist and the read-only split mirror. There is no migration from a prior semver line.

### Install

```bash
composer require symfinity/ui-kernel:^0.1
```

Ensure the [symfinity/recipes](https://github.com/symfinity/recipes) Flex endpoint is configured so the install recipe runs.

### What you get

- Symfony bundle registered for all environments
- Default `config/packages/symfinity_ui_kernel.yaml` in your app (wiring only)
- Built-in themes: `default`, `semantic`, and `utility` lineages (light + dark)
- Overlay tokens (`--ui-overlay-*`, `--ui-backdrop-*`) in generated theme CSS
- Config guard — non-empty `contract:` or `generator:` in app YAML fails at compile time
- Twig helpers: `ui_kernel_css()`, `ui_kernel_theme_boot_script()`, `ui_kernel_active_theme_id()`, `ui_kernel_theme_shell()`
- OKLCH palette generator revision `1` with theme token schema `1.0`
- PHP 8.2+

### Breaking changes from monorepo `main` (pre-tag)

If you tracked `dev-main` before `v0.1.0`:

- Composer alias is `0.1.x-dev` (was `1.x-dev`)
- Public vocabulary uses **Theme** / `default_theme` (legacy "Flavour" naming removed)
- Role component CSS moved to `ux-blocks-*` packages — ui-kernel is tokens + structural globals only

### After upgrading

1. Include `@UiKernel/_head.html.twig` or the two Twig calls in your base layout — [Quick start](quickstart.md)
2. Run `php bin/console debug:config symfinity_ui_kernel`
3. Clear Symfony cache in each environment

## Upgrading from `v0.1.0` to `v0.1.1`

```bash
composer update symfinity/ui-kernel
```

No Flex recipe bump — the `0.1` recipe and `^0.1` constraint are unchanged.

### What changed

- **Theme-tone mono refs** — mono-based semantic roles (e.g. `mono.cool.500` for secondary brand) align to the active lineage tone; warm themes rewrite cool-tinted mono refs; `mono.pure.*` stays achromatic

### Notes

- `composer.json` on `v0.1.1` allows PHP **8.1+** — unintended; use **`v0.1.2`** if you need the documented **8.2+** floor restored
- Overlay tokens, config guard, and slim kernel boundary (no HTTP demo routes in this package) were already in **`v0.1.0`**

### After upgrading

1. Run `php bin/console debug:config symfinity_ui_kernel` and clear cache
2. Spot-check warm/cool mono lineages if you use custom theme packs
3. See [CHANGELOG.md](../CHANGELOG.md) for the full `0.1.1` note

## Upgrading from `v0.1.1` to `v0.1.2`

```bash
composer update symfinity/ui-kernel
```

No Flex recipe bump — the `0.1` recipe and `^0.1` constraint are unchanged.

### What changed

- **Light mode text** — default body copy uses darker `mono.*.900` tokens; muted secondary copy uses `mono.*.500` instead of `.300`; theme CSS sets `color: var(--ui-color-text)` on `:root` / `[data-theme]`
- **PHP 8.2+** — minimum PHP restored in `composer.json` (after the `v0.1.1` `>=8.1` slip)
- **Browser demos** — optional `symfinity/ux-blocks-demo` in Composer `suggest`; theme galleries are not shipped in ui-kernel (see [Installation](installation.md))
- **Handbook** — overlay token docs, version upgrade paths

### After upgrading

1. Run `php bin/console debug:config symfinity_ui_kernel` and clear cache
2. Re-check light-mode pages for text contrast
3. See [CHANGELOG.md](../CHANGELOG.md) for the full `0.1.2` note

## Future releases

See [CHANGELOG.md](../CHANGELOG.md) for version-to-version notes.
