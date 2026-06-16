# Upgrade and migration

## 0.1.1

No breaking changes. Patch release: Flex recipe constraint `^0.1.1` and handbook upgrade guide.

```bash
composer update symfinity/ui-kernel
```

## 0.1.0

Initial public release under `symfinity/ui-kernel`.

```bash
composer require symfinity/ui-kernel
```

The Flex recipe registers the bundle for all environments and copies a minimal app config. See [Installation](installation.md).

If you consumed ui-kernel from a private monorepo path repository during development, switch to the [symfinity/recipes](https://github.com/symfinity/recipes) Flex endpoint before requiring from Packagist.

### Fresh install checklist

1. Add the symfinity/recipes Flex endpoint — [Installation](installation.md).
2. `composer require symfinity/ui-kernel`
3. Include `ui_kernel_theme_boot_script()` and `ui_kernel_css()` in your base layout — [Quick start](quickstart.md).
4. Set `default_theme` / `default_variant` in `config/packages/symfinity_ui_kernel.yaml` — [Configuration](configuration.md).

## See also

- [CHANGELOG](../CHANGELOG.md)
- [Themes](themes.md)
- [GitHub Issues](https://github.com/symfinity/ui-kernel/issues)
