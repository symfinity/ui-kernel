# UI Kernel Web Profiler collector

Dev-only Symfony Web Profiler integration for theme and CSS observability.

## Requirements

- `kernel.debug: true`
- `symfony/web-profiler-bundle` installed and registered in `dev` (Composer **suggest**, not a hard require)

```bash
composer require --dev symfony/web-profiler-bundle
```

## What you get

| Surface | Content |
|---------|---------|
| WDT toolbar | Palette icon + resolved `themeId`; click opens UI Kernel profiler panel |
| Profiler menu | **UI Kernel** row with palette icon (no theme id in nav label) |
| Profiler panel | Resolution table, compact `--ui-color-*` swatch grid (CSS var tooltips), active theme metadata, registered themes list |

## Registration rules

| Rule | Detail |
|------|--------|
| Service id | `ui_kernel` |
| Template | `@UiKernel/Collector/ui_kernel.html.twig` |
| Gating | `kernel.debug` + `WebProfilerBundle` class present (`RegisterProfilerCollectorPass`) |
| Data source | `ActiveThemeContext` — collector MUST NOT parse cookies directly |
| Dependencies | MUST NOT hard-require `symfony/web-profiler-bundle` or `symfinity/ui-profiler` |

Implementation: `src/DataCollector/UiKernelDataCollector.php`, `templates/Collector/`.

## Manual smoke

Org dogfood example:

```bash
make dogfood-serve SLUG=chameleon-showcase
# or SLUG=ux-blocks-demo → /kernel
```

1. Open any HTML route with ui-kernel active.
2. WDT shows palette icon + theme id (e.g. `default-dark`).
3. Click badge → profiler opens on **UI Kernel** panel.
4. Confirm resolution table, color palette swatches, and theme list.
5. Set cookies `symfinity_ui_kernel_lineage=utility`, `symfinity_ui_kernel_scheme=light`; reload — panel updates.

## PHPUnit

```bash
cd src/symfinity
docker compose --env-file .env.docker run --rm -T -w /app php \
  vendor/bin/phpunit packages/ui-kernel/tests/Unit/DataCollector/
```

## Horizon: symfinity/ui-profiler chrome

When `symfinity/ui-profiler` is installed, hosts may later override collector chrome via `templates/chrome/UiKernelBundle/Collector/` while keeping `toolbar`, `menu`, and `panel` blocks compatible. Stock ui-kernel ships `@WebProfiler/Profiler/layout.html.twig` only.
