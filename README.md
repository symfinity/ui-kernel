<div align="center">

# UI Kernel

### Design tokens, themes, and slim CSS generation for Symfony

[![PHP Version](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white)](composer.json)
[![Symfony](https://img.shields.io/badge/Symfony-6.4+-343434?style=flat&logo=symfony&logoColor=white)](composer.json)
<br/>
[![CI](https://github.com/symfinity/ui-kernel/actions/workflows/ci.yml/badge.svg)](https://github.com/symfinity/ui-kernel/actions/workflows/ci.yml)
<br/>
[![Release](https://img.shields.io/packagist/v/symfinity/ui-kernel.svg?style=flat&logo=packagist&logoColor=white)](https://packagist.org/packages/symfinity/ui-kernel)
[![Downloads](https://img.shields.io/packagist/dt/symfinity/ui-kernel.svg?style=flat&logo=packagist&logoColor=white)](https://packagist.org/packages/symfinity/ui-kernel)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat)](LICENSE)

</div>

> [!NOTE]
> **Read-only mirror.**
> See [CONTRIBUTING.md](CONTRIBUTING.md) for how to propose changes.

## Features

- **Design tokens** — `--ui-color-*`, spacing, radius, motion, and focus tokens from W3C DTCG theme layers
- **Built-in themes** — Balanced, Semantic, and Utility lineages (light + dark variants) on disk under `config/themes/{lineage}/`
- **OKLCH palette generator** — shared ramp math; author palette refs, not raw hex, in theme packs
- **Twig integration** — `ui_kernel_css()`, theme boot script, active theme id, theme shell helper
- **Slim kernel boundary** — theme CSS only; component `[data-ui-role]` rules live in `ux-blocks-*` packages. ui-themer consumer themes use `AuthoringThemeConfig` (not the built-in DTCG catalog) — see [Themes](docs/themes.md#ui-themer-boundary).

## Prerequisites

Add the [symfinity/recipes](https://github.com/symfinity/recipes) Flex endpoint to your project's `composer.json` (see [recipes README](https://github.com/symfinity/recipes/blob/main/README.md)) — recipes are not in Symfony's official recipe repository yet.

## Installation

```bash
composer require symfinity/ui-kernel
```

The Flex recipe registers the bundle for all environments and copies a minimal app config. See [Installation](docs/installation.md).

## Quick Start

```twig
{# templates/base.html.twig #}
<head>
    {{ ui_kernel_theme_boot_script() }}
    {{ ui_kernel_css()|raw }}
</head>
```

```yaml
# config/packages/symfinity_ui_kernel.yaml
symfinity_ui_kernel:
    default_theme: semantic
    default_variant: semantic
    schema_version: '1.0'
```

See [Quick start](docs/quickstart.md) for the full walkthrough.

## Documentation

- **[Quick start](docs/quickstart.md)** — theme CSS on every page in minutes
- **[Installation](docs/installation.md)** — Flex, manual setup, Web Profiler (dev)
- **[Configuration](docs/configuration.md)** — app wiring, user tokens, system profile
- **[Themes](docs/themes.md)** — built-in lineages, DTCG on-disk layout, `design_system_id`, dark mode
- **[Font Manager pairing](docs/font-manager-pairing.md)** — optional webfonts
- **[Upgrade](docs/upgrade.md)** — `v0.1.0` baseline, `v0.1.1` theme-tone, `v0.1.2` contrast and docs

## Requirements

- PHP 8.2 or higher
- Symfony 6.4, 7.x, or 8.x
- Twig 3.0 or higher

## Support

- [GitHub Issues](https://github.com/symfinity/ui-kernel/issues)
- [Security](.github/SECURITY.md)
- [Contributing](CONTRIBUTING.md)

## License

[MIT](LICENSE)
