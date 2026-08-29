# Usage

Day-to-day patterns after [Installation](installation.md) and [Quick start](quickstart.md).

## Theme CSS on every page

Emit boot script before generated CSS in your base layout:

```twig
{{ ui_kernel_theme_boot_script() }}
{{ ui_kernel_css()|raw }}
```

Use `ui_kernel_active_theme_id()` in debug panels or admin chrome when you need the resolved lineage id.

## Overrides without forking the bundle

| Need | Mechanism |
|------|-----------|
| Brand colours on built-in lineages | `user_tokens` in app `symfinity_ui_kernel.yaml` |
| Full custom lineage | App-owned files under `config/themes/` + `themes_directory` |
| Author / export packs | Your own YAML packs (preset / tone / semantics schema via `AuthoringThemeConfig`) |

Keep app config minimal — do not copy `contract` or `generator` blocks from the bundle.

## Pair with UX Blocks

Install tier packages (`symfinity/ux-blocks-core`, …) for `[data-ui-role]` CSS. UI Kernel supplies tokens only; component rules live in tier packages.

## See also

- [Themes](themes.md) — built-in lineages and dark mode
- [Configuration](configuration.md) — `schema_version`, `default_physics`, system profile
- [Font Manager pairing](font-manager-pairing.md) — optional webfonts
