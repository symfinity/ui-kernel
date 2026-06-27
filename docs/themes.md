# Themes

Symfony **Twig theme** (`twig.default_path`, FrameworkBundle template paths) is unrelated to **UI Kernel themes** (`data-theme`, `ThemeRegistry`). See [Symfony Twig theming](https://symfony.com/doc/current/templates.html#twig-theme-configuration).

## On-disk layout

Built-in themes ship as W3C DTCG token files — not bespoke `symfinity_ui_kernel.themes` YAML:

```text
config/
├── design-systems/symfinity.dtcg.yaml   # platform semantic vocabulary (eight colours, schema 2.0)
└── themes/{lineage}/
    ├── theme.meta.yaml                  # palette recipe + variant registry
    ├── {variant}.dtcg.yaml              # theme-layer semantic + appearance tokens
    └── …
```

| File | Role |
|------|------|
| `theme.meta.yaml` | `lineage`, optional `design_system_id`, shared `palette`, `variants[]` with `layer_file`, `tone`, `mode` |
| `{variant}.dtcg.yaml` | `theme` layer — semantic colour aliases (`color.primary` → `{color.blue.600}`), spacing, radius, motion |
| `design-systems/{id}.dtcg.yaml` | `design_system` layer — cross-theme vocabulary extensions |

### `design_system_id`

Each lineage **SHOULD** set `design_system_id: symfinity` in `theme.meta.yaml` (default when omitted). The registry loads `config/design-systems/{id}.dtcg.yaml`. Unknown ids fail at stack-build time with `UnknownDesignSystemException`.

Runtime resolution: `base` (OKLCH palette DTCG from generator) ⊕ `design_system` ⊕ `theme` → `LayeredTokenResolver` → `--ui-*` CSS.

**Authoring consumer themes** (export YAML using the bespoke `preset` / `tone` / `semantics` schema via `AuthoringThemeConfig`) do **not** register in `config/themes/`. See [DTCG token core](dtcg-token-core.md) and the authoring boundary below.

## Consumer app overrides

Flex apps may ship DTCG lineages under `config/themes/{lineage}/` (same layout as bundle). App lineages **override** bundle lineages with the same folder name; invalid app lineages are skipped with a warning.

```yaml
# config/packages/symfinity_ui_kernel.yaml
symfinity_ui_kernel:
    themes_directory: '%kernel.project_dir%/config/themes'
```

Merged variants appear in `ThemeRegistry` when app lineages are present.

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
    default_physics: flat
```

## Physics axis (`data-ui-physics`)

Material character — radius, elevation shadow model, motion durations/easing, hover lift — is orthogonal to palette, mode, and Preset (typography + spacing). Three profiles ship in kernel CSS:

| Physics | DOM value | Notes |
|---------|-----------|-------|
| **flat** | `flat` | Default; opaque surfaces, quick motion, no hover lift |
| **glass** | `glass` | Dark-mode only (light + glass corrects to flat) |
| **retro** | `retro` | Zero radius, hard offset shadow, stepped/instant motion |

Set on `[data-ui-root]` (or `html`):

```html
<html data-ui-root data-theme="semantic-dark" data-ui-physics="glass">
```

Author in `theme.meta.yaml`:

```yaml
physics: flat   # lineage default; per-variant override in variants[]
```

`CssGenerator` emits `[data-ui-physics="…"]` blocks with `--ui-physics-*` tokens and bridge aliases to `--ui-motion-*` / `--ui-radius-*`. Complements **109** `data-ui-surface="glass"` (component blur) — not a merge.

## Layout profiles

Built-in themes map to one of two rhythm profiles:

| Profile | Spacing | Typography | Lineages |
|---------|---------|------------|----------|
| **Semantic** | Roomy | 1rem base | `default`, `semantic` pairs |
| **Utility** | Compact | 0.875rem base | `utility` pair |

Radius, shadow, and motion character follow the active **physics** profile (default `flat`). DTCG theme layers still define per-lineage appearance tokens until authors opt into non-flat physics via `data-ui-physics`.

## Dark mode and `data-theme`

The boot script (`ui_kernel_theme_boot_script()`) sets `document.documentElement.dataset.theme` before CSS paints. Pair light/dark ids per lineage (`default` / `default-dark`, etc.).

For `prefers-color-scheme: auto`, the bundle can sync with a server endpoint when configured. Without it, the client resolves light/dark from system preference using the configured lineage pair.

## Generated CSS scope

`CssGenerator` emits:

- Theme tokens (`--ui-color-*`, `--ui-space-*`, `--ui-radius-*`, …)
- Physics blocks (`[data-ui-physics="flat|glass|retro"]` with `--ui-physics-*` + bridged motion/radius)
- Overlay tokens (`--ui-overlay-surface`, `--ui-overlay-border`, `--ui-overlay-shadow`, `--ui-backdrop-color`, `--ui-backdrop-blur`)
- Structural profile globals (breakpoints, z-index ladder, layout roles such as grid/stack)

Mono-based semantic colour refs follow the active **theme tone** (warm/cool/pure families) so text and surface roles stay coherent within a lineage.

It does **not** emit `[data-ui-role]` component rules. Install `symfinity/ux-blocks-*` tier packages for component CSS.

### Authoring theme boundary {#authoring-theme-boundary}

Consumer themes authored with the export YAML pipeline use `AuthoringThemeConfig` + `ThemeTokenResolver` — **not** the built-in DTCG catalog. Authoring packs live outside `config/themes/` and resolve through the authoring pipeline, not `BuiltinDtcgThemeCatalog`.

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

## Semantic colour homonyms (schema 2.0)

| Term | Namespace | Example |
|------|-----------|---------|
| Semantic `neutral` | `data-ui-variant="neutral"` on colour roles | Cancel / chrome button |
| Mono tone `neutral` | `mono.neutral.500` palette ramp | Surface tint — not a button variant |
| Typography `muted` | typography role variant | Helper text (**089**) |
| Structural `text-muted` | `--ui-color-text-muted` | Foreground token — not `data-ui-variant` |
| `ghost` | `data-ui-appearance="ghost"` on Button/Link only | Transparent hover wash — **not** a semantic colour |

## See also

- [Configuration](configuration.md)
- [Quick start](quickstart.md)
