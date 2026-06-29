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

- **Design tokens** — color, spacing, radius, motion, and focus CSS variables from theme packs
- **Built-in themes** — Balanced, Semantic, and Utility lineages with light and dark variants
- **Twig integration** — `ui_kernel_css()`, theme boot script, and theme shell helpers
- **Slim kernel boundary** — theme CSS only; component styles ship in `ux-blocks-*` packages
- **Flex recipe** — bundle and default config wired on install

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
```

See [Quick start](docs/quickstart.md) for the full walkthrough.

## Documentation

- **[Quick start](docs/quickstart.md)** — theme CSS on every page in minutes
- **[Installation](docs/installation.md)** — Flex, manual setup, Web Profiler (dev)
- **[Configuration](docs/configuration.md)** — app wiring, user tokens, system profile
- **[Usage](docs/usage.md)** — daily layout and override patterns
- **[Themes](docs/themes.md)** — built-in lineages, dark mode, and theme packs
- **[Reference](docs/reference.md)** — config root, DTCG layout, and entry points
- **[Troubleshooting](docs/troubleshooting.md)** — stale CSS, upgrades, Web Profiler
- **[Font Manager pairing](docs/font-manager-pairing.md)** — optional webfonts

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
