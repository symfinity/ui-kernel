<div align="center">

# Ui Kernel

### Chameleon UI kernel — design tokens, themes, and role CSS generation

[![PHP Version](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white)](composer.json)
[![Symfony](https://img.shields.io/badge/Symfony-6.4+-343434?style=flat&logo=symfony&logoColor=white)](composer.json)

<br/>
[![PHPUnit](https://github.com/symfinity/symfinity/actions/workflows/phpunit.yml/badge.svg)](https://github.com/symfinity/symfinity/actions/workflows/phpunit.yml)
[![Coverage](https://github.com/symfinity/symfinity/actions/workflows/coverage.yml/badge.svg)](https://github.com/symfinity/symfinity/actions/workflows/coverage.yml)
[![PHPStan](https://github.com/symfinity/symfinity/actions/workflows/phpstan.yml/badge.svg)](https://github.com/symfinity/symfinity/actions/workflows/phpstan.yml)
<br/>
[![Psalm](https://github.com/symfinity/symfinity/actions/workflows/psalm.yml/badge.svg)](https://github.com/symfinity/symfinity/actions/workflows/psalm.yml)
[![Infection](https://github.com/symfinity/symfinity/actions/workflows/infection.yml/badge.svg)](https://github.com/symfinity/symfinity/actions/workflows/infection.yml)
[![Code Style](https://img.shields.io/badge/code%20style-CS%20Fixer-5c4dbc?style=flat)](https://github.com/symfinity/symfinity/actions/workflows/php-cs-fixer.yml)
<br/>
[![Release](https://img.shields.io/packagist/v/symfinity/ui-kernel.svg?style=flat&logo=packagist&logoColor=white)](https://packagist.org/packages/symfinity/ui-kernel)
[![Downloads](https://img.shields.io/packagist/dt/symfinity/ui-kernel.svg?style=flat&logo=packagist&logoColor=white)](https://packagist.org/packages/symfinity/ui-kernel)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat)](LICENSE)

</div>

---

## Documentation

| Topic | Page |
|-------|------|
| Architecture | [docs/architecture.md](docs/architecture.md) |
| Configuration | [docs/configuration.md](docs/configuration.md) |
| Font Manager Pairing | [docs/font-manager-pairing.md](docs/font-manager-pairing.md) |
| Index | [docs/index.md](docs/index.md) |
| Installation | [docs/installation.md](docs/installation.md) |
| Output Channels | [docs/output-channels.md](docs/output-channels.md) |
| Quickstart | [docs/quickstart.md](docs/quickstart.md) |
| Reference | [docs/reference.md](docs/reference.md) |
| Themes | [docs/themes.md](docs/themes.md) |
| Troubleshooting | [docs/troubleshooting.md](docs/troubleshooting.md) |
| Upgrade | [docs/upgrade.md](docs/upgrade.md) |
| Usage | [docs/usage.md](docs/usage.md) |

## Requirements

- PHP 8.2+
- Symfony 6.4+ (Flex recipe when available)

## Install

```bash
composer require symfinity/ui-kernel
```

## Showcase (dev)

With **UiKernelBundle** and **SymfinityUxBlocksCoreBundle** registered (Chameleon default):

```text
GET /ui-kernel/showcase
GET /ui-kernel/showcase?theme=dark
GET /ui-kernel/showcase?carousel=0
GET /_ui/theme.css?theme=bootstrap-like
```

Gallery slots render **symfinity/ux-blocks-core** Twig components (`blocks.*` fragments). Theme rotation exercises kernel token CSS only — gallery markup stays fixed.

Themes are **Symfinity token packs inspired by** common systems — not official Bootstrap or Tailwind. Baseline labels: **Kiroshi** (`default`), **Kiroshi dark** (`default-dark`).

## QA

From the product monorepo root (`src/symfinity/`):

```bash
./sbin/php vendor/bin/mono package:validate packages/ui-kernel/
./sbin/php vendor/bin/mono qa:test
```

Package-only:

```bash
cd packages/ui-kernel && composer install && composer test
```
