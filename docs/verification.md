# Verification

**Integration profile:** P2 — foundation (theme CSS via head partial or Twig helpers).

## Local commands

```bash
composer test
composer phpstan
php bin/console debug:config symfinity_ui_kernel
```

## Clean-app smoke

```bash
composer require symfinity/ui-kernel
```

In your base layout `<head>`, include kernel output:

```twig
{% include '@UiKernel/_head.html.twig' %}
```

Or call the helpers in order:

```twig
{{ ui_kernel_theme_boot_script() }}
{{ ui_kernel_layer_order()|raw }}
{{ ui_kernel_css()|raw }}
```

Confirm generated theme CSS variables on `:root` or `[data-theme="…"]` in page source. When `symfinity/ux-blocks-core` is also installed, tier CSS may be injected automatically — kernel head partial is still required for boot script and layer order.

## See also

- [Quick start](quickstart.md)
- [CHANGELOG](../CHANGELOG.md)
