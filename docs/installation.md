# Installation

## Requirements

PHP 8.2+ and Symfony 6.4.

## Composer

```bash
composer require symfinity/ui-kernel
```

## Symfony Flex

Describe recipe output: `config/packages/`, routes, assets (if any).

## Manual installation

Only when Flex is unavailable: register bundle, copy config skeleton.

## Verify installation

```bash
# example: bin/console debug:config symfinity_* 
```

## Optional: Web Profiler (dev)

For theme/CSS observability in the Symfony debug toolbar and profiler panel, install the profiler bundle in **dev** only:

```bash
composer require --dev symfony/web-profiler-bundle
```

Ensure `WebProfilerBundle` is registered in `config/bundles.php` for `dev` and `framework.profiler.collect` is enabled. Ui Kernel registers `UiKernelDataCollector` automatically when `kernel.debug` is true and the bundle is present. Details: [profiler.md](profiler.md).

## Next steps

[Quick start](quickstart.md).
