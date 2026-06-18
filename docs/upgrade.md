# Upgrade and migration

## 0.1.2

Patch release after [v0.1.1](https://github.com/symfinity/ui-kernel/releases/tag/v0.1.1). Optional consumer theme overrides and `--ui-color-button-text`; yellow ramp CSS may differ from v0.1.1.

```bash
composer update symfinity/ui-kernel
```

After upgrade:

1. Clear Symfony cache if theme CSS is cached in your environment.
2. Re-check snapshots that assert exact `--ui-color-*` values — yellow ramp steps changed; new `--ui-color-button-text` appears in generated CSS.
3. To ship app-owned DTCG lineages, add files under `config/themes/{lineage}/` and set `themes_directory` if not using the default — see [Themes](themes.md#consumer-app-overrides).
4. Existing apps without `config/themes/` need no config changes beyond the Flex-copied default.

## 0.1.1

Patch release after [v0.1.0](https://github.com/symfinity/ui-kernel/releases/tag/v0.1.0). PHP APIs are unchanged; **generated palette CSS is not identical to v0.1.0** (perceptual midtone policy, dark-tail curve, bundle mono hue centralization — all in-place at `generator.palette.revision: 1`).

```bash
composer update symfinity/ui-kernel
```

After upgrade:

1. Clear Symfony cache if theme CSS is cached in your environment.
2. Re-check pages or snapshots that assert exact `--ui-color-*` values — hue ramps and tinted mono steps were recalculated in place at `generator.palette.revision: 1`.
3. No manifest or config key renames; existing `symfinity_ui_kernel.yaml` trees remain valid.

## 0.1.0

Initial public release under `symfinity/ui-kernel`.

```bash
composer require symfinity/ui-kernel
```

The Flex recipe registers the bundle for all environments and copies a minimal app config. See [Installation](installation.md).

If you consumed ui-kernel from a private monorepo path repository during development, switch to the [symfinity/recipes](https://github.com/symfinity/recipes) Flex endpoint before requiring from Packagist.

### Fresh install checklist

1. Add the symfinity/recipes Flex endpoint — [Installation](installation.md).
2. `composer require symfinity/ui-kernel`
3. Include `ui_kernel_theme_boot_script()` and `ui_kernel_css()` in your base layout — [Quick start](quickstart.md).
4. Set `default_theme` / `default_variant` in `config/packages/symfinity_ui_kernel.yaml` — [Configuration](configuration.md).

## See also

- [CHANGELOG](../CHANGELOG.md)
- [Themes](themes.md)
- [GitHub Issues](https://github.com/symfinity/ui-kernel/issues)
