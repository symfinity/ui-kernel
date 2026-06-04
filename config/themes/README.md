# Built-in UI Kernel themes

One YAML file per **lineage** (schema **1.0**). Palette contract and generator live in `../packages/symfinity_ui_kernel.yaml`.

Files whose basename starts with `_` are reference-only — not loaded.

```yaml
symfinity_ui_kernel:
  themes:
    default:
      palette:
        hues: { … }
        mono:
          pure: { hue: 0.0, saturation: 0.0 }
          …
      tokens:
        space: { xs: 0.25rem, … }
        radius: { … }
        font:
          family: { sans: … }
          size: { … }
        motion:
          duration: { … }
        focus:
          ring: { width: … }
      variants:
        default:
          label: …
          tone: cool
          mode: light
          colors:
            brand: { primary: sky.600, … }
            surface: { base: …, elevated: …, overlay: … }
            …
        default_dark:
          extends: default
          label: …
          mode: dark
          colors:
            surface: { base: … }
            …
```

| File | Lineage key | Variant keys |
|------|-------------|--------------|
| `default.yaml` | `default` | `default`, `default_dark` |
| `semantic.yaml` | `semantic` | `semantic`, `semantic_dark` |
| `utility.yaml` | `utility` | `utility`, `utility_dark` |
| `kiroshi.yaml` | `kiroshi` | `kiroshi`, `kiroshi_dark` |

Variant YAML keys use underscores; public theme `id` is kebab-case (`default_dark` → `default-dark`). Layout profile is derived from lineage — do not set `layout` on variants.

Loaded by `BuiltinThemeCatalog` → `ThemeYamlNormalizer` → `ThemeConfig` / `ThemeTokenResolver`.
