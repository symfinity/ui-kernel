# Configuration

UI Kernel splits **reference data** (palette contract and built-in themes inside the bundle) from **app wiring** (your Symfony config).

## Files

| File | Role |
|------|------|
| Bundle `config/packages/symfinity_ui_kernel.yaml` | `contract.palette`, `generator.palette`, defaults — **do not copy into your app** |
| Bundle `config/themes/*.yaml` | Built-in theme lineages (`default`, `semantic`, `utility`) |
| App `config/packages/symfinity_ui_kernel.yaml` | Overrides only — see below |

After install, keep a **minimal** app file. Palette math and built-in theme trees stay in the bundle.

## App options (`symfinity_ui_kernel`)

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `default_theme` | string | `default` | Active built-in theme lineage (`data-theme` anchor) |
| `default_variant` | string | `default` | Default variant key within the lineage |
| `schema_version` | string | `2.0` | Output token schema (`2.0` — semantic colour vocabulary **115**) |
| `default_physics` | string | `flat` | Physics axis: `flat`, `glass`, or `retro` (**111**) |
| `user_tokens` | map | `{}` | Partial `--ui-*` overrides on the active theme |
| `system_profile.id` | string | `ui-kernel-default` | Structural profile id |
| `system_profile.columns` | int | `12` | Grid columns |
| `system_profile.breakpoints` | map | (profile default) | Breakpoint name to px |
| `system_profile.container_max_widths` | map | (profile default) | Breakpoint name to max width px |

Example (app layer):

```yaml
symfinity_ui_kernel:
    default_theme: semantic
    default_variant: semantic
    schema_version: '2.0'
    default_physics: flat
    user_tokens:
        '--ui-color-primary': '#336699'
    system_profile:
        breakpoints:
            md: 768
```

**Do not** put `contract.palette`, `generator.palette`, or full built-in theme trees in the app file unless you maintain a private fork of the bundle.

Applications **cannot** override `contract` or `generator` keys — the extension rejects non-empty values at compile time (enforced since `v0.1.0`).

## Palette generator (bundle SSOT)

The bundle ships an OKLCH palette generator (`generator.palette.revision`) shared by all built-in themes. Theme YAML supplies hue angles and mono spice; the generator resolves palette refs such as `blue.600` into `--ui-color-*` values.

Integrators author **palette refs** in custom theme packs, not raw hex in built-in lineage files.

Custom packs (ui-themer, agency YAML) resolve ramps via **live OKLCH** — they do not inherit frozen hex anchor tables. Built-in lineages use the same live OKLCH generator (`generator.palette.revision` stays `1`); optional sparse `palette.anchors` in `theme.meta.yaml` still override individual refs.

## Custom brand themes

Use **symfinity/ui-themer** (or your own YAML packs) for brand-specific themes. Those files are **not** loaded from `ui-kernel/config/themes/`.

Authoring shape: `version`, `id`, `schema_version`, `preset` (lineage: `default`, `semantic`, or `utility`), `tone`, and semantic colour refs.

## Environment variables

No dedicated env vars in v0.1; use Symfony `symfinity_ui_kernel` config as usual.

## See also

- [Themes](themes.md)
- [Quick start](quickstart.md)
