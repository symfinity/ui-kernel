---
source_links:
  - kind: php
    label: UiKernelBundle.php
    path: src/UiKernelBundle.php
  - kind: php
    label: CssGenerator.php
    path: src/Css/CssGenerator.php
---
# Installation

## Prerequisites

Add the [symfinity/recipes](https://github.com/symfinity/recipes) Flex endpoint to your project's `composer.json` (see [recipes README](https://github.com/symfinity/recipes/blob/main/README.md)).

## Composer

```bash
composer require symfinity/ui-kernel
```

## Symfony Flex

The recipe applies:

- `config/packages/symfinity_ui_kernel.yaml` from the package default
- Bundle registration for **all** environments (`all`)

Palette contract, generator settings, and built-in theme YAML stay in the bundle. Your app file should only override wiring options such as `default_theme` and `user_tokens` — see [Configuration](configuration.md).

## Manual installation

When Flex is unavailable:

1. `composer require symfinity/ui-kernel`
2. Register `Symfinity\UiKernel\UiKernelBundle` in `config/bundles.php`
3. Copy `config/packages/symfinity_ui_kernel.yaml` from the package into your project

## Verify installation

```bash
php bin/console debug:config symfinity_ui_kernel
```

## Optional: Web Profiler (dev)

For theme and CSS observability in the Symfony debug toolbar, install the profiler bundle in **dev** only:

```bash
composer require --dev symfony/web-profiler-bundle
```

Ensure `WebProfilerBundle` is registered in `config/bundles.php` for `dev` and that `framework.profiler.collect` is enabled. UI Kernel registers `UiKernelDataCollector` automatically when `kernel.debug` is true and the profiler bundle is present.

## Optional: browser demos (dev/test)

Theme galleries and kernel showcase HTTP routes ship in **symfinity/ux-blocks-demo**, not in ui-kernel. Install only in dev/test:

```bash
composer require --dev symfinity/ux-blocks-demo
```

## Next steps

[Quick start](quickstart.md).
