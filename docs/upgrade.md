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
- Twig helpers: `ui_kernel_css()`, `ui_kernel_theme_boot_script()`, `ui_kernel_active_theme_id()`, `ui_kernel_theme_shell()`
- OKLCH palette generator revision `1` with theme token schema `1.0`

### Breaking changes from monorepo `main` (pre-tag)

If you tracked `dev-main` before `v0.1.0`:

- Composer alias is `0.1.x-dev` (was `1.x-dev`)
- Public vocabulary uses **Theme** / `default_theme` (legacy "Flavour" naming removed)
- Role component CSS moved to `ux-blocks-*` packages — ui-kernel is tokens + structural globals only

### After upgrading

1. Include `@UiKernel/_head.html.twig` or the two Twig calls in your base layout — [Quick start](quickstart.md)
2. Run `php bin/console debug:config symfinity_ui_kernel`
3. Clear Symfony cache in each environment

## Future releases

See [CHANGELOG.md](../CHANGELOG.md) for version-to-version notes.
