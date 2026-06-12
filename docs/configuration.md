# Configuration

UI Kernel splits **reference data** (palette contract in bundle config + themes in `config/themes/`) from **app wiring** (Symfony config in your project).

Normative map: [theme-vocabulary](../../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/theme-vocabulary.md) · [PRODUCT-ui-kernel-config-layers](../../../../../classified/explore/PRODUCT-ui-kernel-config-layers.md).

## Files

| File | Role |
|------|------|
| `config/packages/symfinity_ui_kernel.yaml` (bundle) | `contract.palette`, `generator.palette`, `default_theme`, `default_variant`, `schema_version` |
| `config/themes/*.yaml` (bundle) | `symfinity_ui_kernel.themes` — built-in looks (one file per lineage) |
| `config/packages/symfinity_ui_kernel.yaml` (your app) | **Overrides only** — see below |

After install, dogfood and sibling apps use a **minimal** app file. Palette and built-in theme trees stay in the bundle only.

## App options (`symfinity_ui_kernel`)

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `default_theme` | string | `default` | Active built-in theme lineage (`data-theme` anchor) |
| `default_variant` | string | `default` | Default variant key within the lineage |
| `schema_version` | string | `1.0` | Output token schema (only `1.0` supported) |
| `user_tokens` | map | `{}` | Partial `--ui-*` overrides on the active theme |
| `system_profile.id` | string | `chameleon-default` | Structural profile id |
| `system_profile.columns` | int | `12` | Grid columns |
| `system_profile.breakpoints` | map | (profile default) | Breakpoint name → px |
| `system_profile.container_max_widths` | map | (profile default) | Breakpoint name → max width px |

Example (app layer):

```yaml
symfinity_ui_kernel:
  default_theme: semantic
  default_variant: semantic
  schema_version: '1.0'
  user_tokens:
    '--ui-color-primary': '#336699'   # rare — prefer palette refs in custom theme packs
  system_profile:
    breakpoints:
      md: 768
```

**Do not** put `contract.palette`, `generator.palette`, or built-in theme trees in the app file unless you maintain a private kernel fork.

## Generator palette — L / C / H responsibility

Bundle `generator.palette` supplies shared ramp math; theme lineage YAML supplies brand hue angles and mono spice.

| OKLCH | Source | Config path |
|-------|--------|-------------|
| **L** | Bundle generator | `generator.palette.lightness_curve.{curve}[i]` (index aligns with `contract.palette.levels`) |
| **C** (hue ramps) | Bundle generator | `generator.palette.hue_chroma.{family}` |
| **C** (mono spice) | Theme lineage | `themes.{lineage}.palette.mono.{tone}.saturation` |
| **H** (hue ramps) | Theme lineage | `themes.{lineage}.palette.hues.{family}` |
| **H** (mono spice) | Theme lineage | `themes.{lineage}.palette.mono.{tone}.hue` |

Normative detail: [generator-palette-config](../../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/generator-palette-config.md).

## Built-in theme shape (`config/themes/{lineage}.yaml`)

See [config/themes/README.md](../config/themes/README.md). Summary:

- `symfinity_ui_kernel.themes.{lineage}.palette` — `hues` + `mono` (not flat `hue_base` in YAML)
- Grouped `tokens` (space, radius, font, motion, focus, …)
- `variants.{key}` — `label`, `tone`, optional `mode`, nested `colors`, optional `extends`, `scroll_motion`, `backdrop_blur`
- Layout profile is derived from lineage (`default`/`semantic` → Semantic, `utility` → Utility)

## Custom brand themes

Use **symfinity/ui-themer** (or handbook packs) — YAML with `version`, `id`, `schema_version`, `preset` (lineage: `default`, `semantic`, `utility`), `tone`, `semantics`. Those files are **not** loaded from `ui-kernel/config/themes/`.

## Environment variables

No dedicated env vars in v0; use Symfony `symfinity_ui_kernel` config as usual.

## See also

- [Themes](themes.md)
- [Architecture](architecture.md)
- [Usage](usage.md)
