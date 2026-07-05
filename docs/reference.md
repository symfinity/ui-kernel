# Reference

## Configuration root

`config/packages/symfinity_ui_kernel.yaml` — see [configuration.md](configuration.md) for the full option list.

## Theme pack layout (DTCG)

```text
config/themes/{theme-id}/
├── symfinity.dtcg.yaml      # or app-named *.dtcg.yaml
└── …
```

Built-in themes ship inside the bundle under `Resources/themes/`. Consumer apps override or extend via `symfinity_ui_kernel.themes_directory`.

## Key PHP entry points (split repo)

| Area | Path |
|------|------|
| Bundle | `src/UiKernelBundle.php` |
| CSS generation | `src/Css/CssGenerator.php` |
| Cascade layers | `src/Css/CascadeLayerOrder.php`, `src/Css/CascadeLayerWrap.php` |
| Theme resolution | `src/Theme/RegistryThemeResolver.php` |
| Profiler collector | `src/DataCollector/UiKernelDataCollector.php` |

## Twig helpers

| Function | Role |
|----------|------|
| `ui_kernel_theme_boot_script()` | Inline script — active theme before paint |
| `ui_kernel_layer_order()` | `@layer` declaration for the platform stack |
| `ui_kernel_css()` | Generated theme + profile CSS |
| `ui_kernel_active_theme_id()` | Active built-in theme id |
| `ui_kernel_theme_shell()` | Theme shell markup for layout chrome |

Prefer `{% include '@UiKernel/_head.html.twig' %}` in `<head>` when you do not customize the call order.

## Semantic tokens (schema 2.0)

Eight canonical colours (`accent`, `neutral`, …), appearances (`solid`, `soft`, `ghost`, …), and physics ids (`flat`, `glass`, `retro`) — see [Themes](themes.md) and [Configuration](configuration.md).

## Related packages

| Package | Role |
|---------|------|
| `symfinity/ui-themer` | Author/import theme packs |
| `symfinity/ux-blocks-core` | Role CSS consuming kernel tokens |
| `symfinity/symfinity-docs` | Public handbook runtime |
