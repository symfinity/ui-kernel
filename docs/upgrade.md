# Upgrade and migration

## 0.2.3

Patch release after [v0.2.2](https://github.com/symfinity/ui-kernel/releases/tag/v0.2.2). Split mirror CI only — no theme runtime or API changes.

```bash
composer update symfinity/ui-kernel
```

No config or DTCG file changes required.

## 0.2.2

Patch release after [v0.2.1](https://github.com/symfinity/ui-kernel/releases/tag/v0.2.1). Handbook-only — no theme runtime or API changes.

```bash
composer update symfinity/ui-kernel
```

No config or DTCG file changes required.

## 0.2.1

Patch release after [v0.2.0](https://github.com/symfinity/ui-kernel/releases/tag/v0.2.0). Built-in theme file rename and handbook alignment — no semantic-colour or physics-axis changes.

```bash
composer update symfinity/ui-kernel
```

After upgrade:

1. If you copied the bundle default DTCG file into your app, rename `config/themes/default/chameleon.dtcg.yaml` → `symfinity.dtcg.yaml` (or re-copy from the package).
2. Clear Symfony cache if theme CSS is cached in dev.
3. No `schema_version`, variant slug, or physics changes since v0.2.0.

## 0.2.0

Minor release after [v0.1.2](https://github.com/symfinity/ui-kernel/releases/tag/v0.1.2). Semantic colour vocabulary v2, physics axis, and compound shadows. **Breaking** for custom DTCG themes and CSS snapshots still on schema `1.0` or `tertiary` / `ghost` semantic colours.

```bash
composer require symfinity/ui-kernel:^0.2
```

After upgrade:

1. Set `schema_version: '2.0'` in `config/packages/symfinity_ui_kernel.yaml` (Flex default on fresh copy).
2. In custom `config/themes/` DTCG files: rename `color.tertiary` → `color.accent`; remove `color.ghost`; add `color.neutral` where you need achromatic chrome.
3. In Twig or PHP that passed `variant="ghost"` or `variant="tertiary"` to ux-blocks roles: use `variant="neutral"` + `appearance="ghost"` (Button/Link) or `variant="accent"` — requires **`symfinity/ux-blocks-core` `^0.2`**.
4. Optional: set `default_physics` (`flat`, `glass`, `retro`) and expose `data-ui-physics` on your layout root — see [Themes](themes.md#physics-axis-data-ui-physics).
5. Clear Symfony cache; re-run visual or PHPUnit snapshots that assert exact `--ui-color-*` or `--ui-shadow-*` values.

## 0.1.2

Patch release after [v0.1.1](https://github.com/symfinity/ui-kernel/releases/tag/v0.1.1). Optional consumer theme overrides and `--ui-color-button-text`; yellow ramp CSS may differ from v0.1.1.

```bash
composer update symfinity/ui-kernel
```

After upgrade:

1. Clear Symfony cache if theme CSS is cached in your environment.
2. Re-check snapshots that assert exact `--ui-color-*` values — yellow ramp steps changed; new `--ui-color-button-text` appears in generated CSS.
3. To ship app-owned DTCG lineages, add files under `config/themes/{lineage}/` and set `themes_directory` if not using the default — see [Themes](themes.md) (Consumer app overrides).
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

- [CHANGELOG](https://github.com/symfinity/ui-kernel/blob/main/CHANGELOG.md)
- [Themes](themes.md)
- [GitHub Issues](https://github.com/symfinity/ui-kernel/issues)
