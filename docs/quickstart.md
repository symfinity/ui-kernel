# Quick start

Get theme CSS on every page in a few minutes.

## Installation

```bash
composer require symfinity/ui-kernel
```

The Flex recipe registers the bundle and copies a minimal app config file.

## 1. Include kernel head partial

In your base layout `<head>`, emit the boot script and generated CSS **in this order** (boot script first avoids a flash of wrong theme):

```twig
{# templates/base.html.twig #}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{% block title %}My app{% endblock %}</title>
    {{ ui_kernel_theme_boot_script() }}
    {{ ui_kernel_css()|raw }}
    {% block stylesheets %}{% endblock %}
</head>
<body>
    {% block body %}{% endblock %}
</body>
</html>
```

The bundle also ships `@UiKernel/_head.html.twig` with the same two calls if you prefer an include:

```twig
{% include '@UiKernel/_head.html.twig' %}
```

## 2. Pick a built-in theme

Set the active theme lineage in your app config:

```yaml
# config/packages/symfinity_ui_kernel.yaml
symfinity_ui_kernel:
    default_theme: semantic
    default_variant: semantic
    schema_version: '2.0'
```

Reload the page — `:root` receives `--ui-*` design tokens for the selected theme. Built-in ids include `default`, `default-dark`, `semantic`, `semantic-dark`, `utility`, and `utility-dark`. See [Themes](themes.md).

## 3. Inspect the active theme (optional)

```twig
<p>Active theme: {{ ui_kernel_active_theme_id() }}</p>
```

## Complete minimal example

```yaml
# config/packages/symfinity_ui_kernel.yaml
symfinity_ui_kernel:
    default_theme: default
    default_variant: default
    schema_version: '2.0'
    user_tokens: {}
```

```twig
{# templates/demo.html.twig #}
<!DOCTYPE html>
<html lang="en">
<head>
    {{ ui_kernel_theme_boot_script() }}
    {{ ui_kernel_css()|raw }}
</head>
<body>
    <main style="padding: var(--ui-space-lg); font-family: var(--ui-font-family-sans);">
        <h1 style="color: var(--ui-color-primary);">Hello, UI Kernel</h1>
        <p style="color: var(--ui-color-text-muted);">Tokens come from ui-kernel CSS.</p>
    </main>
</body>
</html>
```

## Component styling

UI Kernel emits **theme tokens and structural profile globals only**. `[data-ui-role]` component rules ship in separate `symfinity/ux-blocks-*` packages. Install the tier packages you need alongside ui-kernel.

## Next steps

- [Configuration](configuration.md) — `user_tokens`, breakpoints, system profile
- [Themes](themes.md) — built-in lineages, dark mode, layout profiles
- [Font Manager pairing](font-manager-pairing.md) — optional webfonts

## See also

- [CHANGELOG](https://github.com/symfinity/ui-kernel/blob/main/CHANGELOG.md) — version history
- [Contributing](https://github.com/symfinity/ui-kernel/blob/main/CONTRIBUTING.md) — how to contribute
- [GitHub Issues](https://github.com/symfinity/ui-kernel/issues) — bug reports
