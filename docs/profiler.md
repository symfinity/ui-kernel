# Web Profiler integration

UI Kernel ships a Symfony **DataCollector** for dev-only theme and CSS observability. It requires `symfony/web-profiler-bundle` as an optional dev dependency — see [installation.md](installation.md).

## What you get

When `kernel.debug` is true and `WebProfilerBundle` is present, the toolbar shows a **palette icon** with the active theme id (e.g. `semantic-dark`). Click the badge to open the profiler panel with lineage, colour scheme, generated CSS size, and theme count.

| Asset | Location |
|-------|----------|
| Collector | `src/DataCollector/UiKernelDataCollector.php` |
| Conditional registration | `src/DependencyInjection/Compiler/RegisterProfilerCollectorPass.php` |
| Templates | `templates/Collector/ui_kernel.html.twig`, `icon.svg` |

Collector id: **`ui_kernel`**.

## Install

```bash
composer require --dev symfony/web-profiler-bundle
```

Register `WebProfilerBundle` in `config/bundles.php` for `dev` and enable `framework.profiler.collect`.

## Verify in the browser

1. Load any page that includes `ui_kernel_css()` in dev.
2. Confirm the Web Debug Toolbar shows the UI Kernel badge and theme id.
3. Open the UI Kernel profiler panel — lineage, scheme, and CSS byte count should match your config.
4. Change theme cookies or config (`default_theme`, `default_variant`); reload and confirm the panel updates.

### With symfinity/ui-profiler

When [symfinity/ui-profiler](../../ui-profiler/README.md) is installed, Chameleon chrome may override profiler layout via bundle template precedence. The kernel collector still registers and reports theme data; visual chrome may differ.

### Expected behaviour

| Case | Expected |
|------|----------|
| `APP_ENV=prod` | No UI Kernel collector |
| WebProfilerBundle absent | No collector service; app boots |
| API route without theme HTML | Toolbar shows `n/a` or minimal payload |

## See also

- [Installation](installation.md) — optional Web Profiler setup
- [Quick start](quickstart.md) — include theme CSS on every page
