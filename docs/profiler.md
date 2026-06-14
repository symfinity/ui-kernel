# Web Profiler integration

UI Kernel ships a Symfony **DataCollector** for dev-only theme and CSS observability. It requires `symfony/web-profiler-bundle` as an optional dev dependency — see [installation.md](installation.md).

## Contracts

| Topic | Path |
|-------|------|
| Collector behaviour | [web-profiler-data-collector.md](../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/web-profiler-data-collector.md) |
| Twig templates + icon | [collector-templates.md](../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/collector-templates.md) |
| Ownership vs ui-profiler | [profiler-collector-ownership.md](../../../../specs/symfinity/symfinity/_org/contracts/ui-profiler/profiler-collector-ownership.md) |

## Implementation

| Asset | Location |
|-------|----------|
| Collector | `src/DataCollector/UiKernelDataCollector.php` |
| Conditional registration | `src/DependencyInjection/Compiler/RegisterProfilerCollectorPass.php` |
| Templates | `templates/Collector/ui_kernel.html.twig`, `icon.svg` |

Registration gates: `kernel.debug` **and** `WebProfilerBundle` present. Collector id: **`ui_kernel`**.

## PHPUnit (monorepo)

```bash
cd src/symfinity
docker compose --env-file .env.docker run --rm -T -w /app php php vendor/bin/phpunit packages/ui-kernel/tests/Unit/DataCollector/
```

## Manual — WDT + profiler panel (dogfood)

Prerequisite: dogfood app with ui-kernel + WebProfilerBundle (`SLUG=ui-lab`).

```bash
make dogfood-serve SLUG=ui-lab
```

1. Open `/`, `/kernel`, or `/ui-themer` in the browser.
2. Confirm WDT shows **palette icon** + theme id (e.g. `semantic-dark`).
3. Click UI Kernel badge → profiler opens on UI Kernel panel.
4. Verify panel shows lineage, scheme, CSS bytes, theme count.
5. Set cookies `symfinity_ui_kernel_lineage=utility`, `symfinity_ui_kernel_scheme=light`; reload — panel updates.

### ui-profiler coexistence

With `symfinity/ui-profiler` installed, repeat the steps above — collector MUST render without PHP errors. Visual chrome may differ (Chameleon); functional blocks unchanged.

### Negative checks

| Case | Expected |
|------|----------|
| `APP_ENV=prod` | No UI Kernel collector |
| WebProfilerBundle absent | No collector service; app boots |
| API route without theme HTML | Toolbar shows `n/a` or minimal payload |

## Horizon — `symfinity/ui-profiler`

When [symfinity/ui-profiler](../../ui-profiler/README.md) is installed, Chameleon chrome may override profiler layout via bundle template precedence. ui-profiler may register an enriched collector with the same id; see [third-party-collectors](../../ui-profiler/docs/third-party-collectors.md).

Stock `@WebProfiler/Profiler/layout.html.twig` is the default for apps without ui-profiler.
