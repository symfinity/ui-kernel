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
| Theme resolution | `src/Theme/RegistryThemeResolver.php` |
| Profiler collector | `src/DataCollector/UiKernelDataCollector.php` |

## Semantic tokens (schema 2.0)

Eight canonical colours (`accent`, `neutral`, …), appearances (`solid`, `soft`, `ghost`, …), and physics ids (`flat`, `glass`, `retro`) — see [Themes](themes.md) and [Configuration](configuration.md).

## Related packages

| Package | Role |
|---------|------|
| `symfinity/ui-themer` | Author/import theme packs |
| `symfinity/ux-blocks-core` | Role CSS consuming kernel tokens |
| `symfinity/symfinity-docs` | Public handbook runtime |
