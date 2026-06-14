# Themes

Symfony **Twig theme** (`twig.default_path`, FrameworkBundle template paths) is unrelated to **UI Kernel themes** (`data-theme`, `ThemeRegistry`). See [Symfony Twig theming](https://symfony.com/doc/current/templates.html#twig-theme-configuration).

## Built-in theme ids

| Id | Label | Notes |
|----|-------|-------|
| `default` | Balanced | Light neutral baseline |
| `default-dark` | Balanced dark | Dark neutral baseline |
| `semantic` | Semantic | Bootstrap-inspired semantic colours, roomy spacing |
| `semantic-dark` | Semantic dark | Dark semantic palette |
| `utility` | Utility | Tailwind-inspired slate/blue palette, compact spacing |
| `utility-dark` | Utility dark | Dark utility palette |

Set the active lineage in config:

```yaml
symfinity_ui_kernel:
    default_theme: semantic
    default_variant: semantic
```

## Layout profiles

Built-in themes map to one of two rhythm profiles:

| Profile | Spacing | Radius | Motion | Lineages |
|---------|---------|--------|--------|----------|
| **Semantic** | Roomy | Rounded | 200ms | `default`, `semantic` pairs |
| **Utility** | Compact | Subtle | 150ms | `utility` pair |

Theme swap changes **semantic role colours**; layout rhythm follows the lineage profile.

## Dark mode and `data-theme`

The boot script (`ui_kernel_theme_boot_script()`) sets `document.documentElement.dataset.theme` before CSS paints. Pair light/dark ids per lineage (`default` / `default-dark`, etc.).

For `prefers-color-scheme: auto`, the bundle can sync with a server endpoint when configured. Without it, the client resolves light/dark from system preference using the configured lineage pair.

## Generated CSS scope

`CssGenerator` emits:

- Theme tokens (`--ui-color-*`, `--ui-space-*`, `--ui-radius-*`, …)
- Overlay tokens (`--ui-overlay-surface`, `--ui-overlay-border`, `--ui-overlay-shadow`, `--ui-backdrop-color`, `--ui-backdrop-blur`)
- Structural profile globals (breakpoints, z-index ladder, layout roles such as grid/stack)

Mono-based semantic colour refs follow the active **theme tone** (warm/cool/pure families) so text and surface roles stay coherent within a lineage.

It does **not** emit `[data-ui-role]` component rules. Install `symfinity/ux-blocks-*` tier packages for component CSS.

## User token overrides

Override public `--ui-*` keys at deploy time without forking theme YAML:

```yaml
symfinity_ui_kernel:
    default_theme: semantic
    user_tokens:
        '--ui-color-primary': '#1a4fd6'
        '--ui-color-secondary': '#6c757d'
```

Unknown keys are rejected at merge time.

## System profile overrides

Adjust breakpoints or container widths via config (not `user_tokens`):

```yaml
symfinity_ui_kernel:
    system_profile:
        breakpoints:
            md: 800
```

When caching generated CSS, include profile identity in your cache key alongside theme id and user token hash.

## Fonts

Built-in themes use **system font stacks** for `--ui-font-family-sans` and `--ui-font-family-mono`. Optional webfont loading via **symfinity/font-manager**: [font-manager-pairing.md](font-manager-pairing.md).

## See also

- [Configuration](configuration.md)
- [Quick start](quickstart.md)
