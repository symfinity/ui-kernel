# UI Kernel Web Profiler collector

**Relocated (068):** The UI Kernel Web Profiler collector lives in **`symfinity/ui-profiler`**, not in ui-kernel.

| Artifact | Package |
|----------|---------|
| `UiKernelDataCollector` | `packages/ui-profiler/src/DataCollector/` |
| Collector Twig | `packages/ui-profiler/templates/Collector/` |
| Registration pass | `RegisterUiKernelCollectorPass` in ui-profiler |

## Requirements

- `kernel.debug: true`
- `symfony/web-profiler-bundle` installed and registered in `dev`
- `symfinity/ui-profiler` installed (Chameleon dogfood labs include it by default)

## Manual smoke

```bash
make dogfood-serve SLUG=chameleon-showcase
# or SLUG=ui-lab
```

1. Open any HTML route with ui-kernel active.
2. WDT shows palette icon + theme id (e.g. `default-dark`).
3. Click badge → profiler opens on **UI Kernel** panel.
4. Confirm resolution table, color palette swatches, and theme list.

## PHPUnit

```bash
cd src/symfinity
./bin/php vendor/bin/phpunit packages/ui-profiler/tests/Unit/DataCollector/
```
