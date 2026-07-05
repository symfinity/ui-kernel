# Font Manager pairing (optional)

**symfinity/ui-kernel** does **not** require **symfinity/font-manager**. Generated CSS always defines:

- `--ui-font-family-sans`
- `--ui-font-family-mono`

Values use system stacks until you wire font-manager.

## Seamless pairing (recommended)

When both packages are installed:

1. `composer require symfinity/font-manager` (suggested in `composer.json`)
2. Enable the bridge in font-manager config:

```yaml
# config/packages/symfinity_font_manager.yaml
symfinity_font_manager:
    ui_kernel_bridge: true
```

3. In your layout `<head>`:

```twig
{{ font_manager('Your Sans', '400 700', 'normal', false, 'swap') }}
{{ font_manager('Your Mono', '400 500', 'normal', true, 'swap') }}
{{ ui_kernel_css('default-dark')|raw }}
{{ font_manager_ui_kernel_bridge()|raw }}
```

4. Use **`var(--ui-font-family-sans)`** and **`var(--ui-font-family-mono)`** in app CSS and Twig — not hardcoded family names.

Full detail: font-manager [font-manager-kernel-pairing.md](https://github.com/symfinity/font-manager/blob/main/docs/font-manager-kernel-pairing.md).

### `font_manager()` parameter order

Runtime order is **`monospace` before `display`**:

```twig
font_manager(name, weights, styles, monospace, display, provider)
```

Wrong order (e.g. `'swap', true`) silently treats sans fonts as monospace.

## When font-manager is absent

Applications remain legible with system stacks — ui-kernel does not ship `@font-face` rules.

## Non-goals (v0.1.0+)

- Runtime auto-detect of font-manager inside ui-kernel PHP
- Flex recipe requiring font-manager
- Bundled font files inside ui-kernel

## See also

- [Themes](themes.md)
- [Configuration](configuration.md)
