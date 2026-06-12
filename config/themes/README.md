# Built-in UI Kernel themes

One YAML file per **lineage** (schema **1.0**). Palette contract, generator revision, and freeze policy live in `../packages/symfinity_ui_kernel.yaml` and [palette-freeze](../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/palette-freeze.md).

Files whose basename starts with `_` are reference-only — not loaded.

## COLOR FREEZE v1 (2026-06-12)

Built-in palette visuals are locked. Change only after operator **palette-freeze lift** — see contract + `PaletteFreezeTest`.

## Lineages (frozen 2026-06-12)

| File | `anchor_profile` | Label |
|------|------------------|-------|
| `default.yaml` | `balanced` | Balanced |
| `semantic.yaml` | `bootstrap-5.3` | Semantic |
| `utility.yaml` | `tailwind-v4` | Utility |

Each profile ships **190** materialized palette refs (130 hue + 60 mono). Resolved colours are `#hex` SSOT — not OKLCH-generated at runtime for contract levels.

## YAML shape

```yaml
symfinity_ui_kernel:
  themes:
    default:
      palette:
        anchor_profile: balanced
        hues: { … }      # fallback / custom themes only
        mono:
          cool: { hue: 240.0, saturation: 4.5 }
          …
      tokens: { … }
      variants:
        default:
          colors:
            brand: { primary: blue.600, … }
            …
```

Variant keys use underscores; public theme id is kebab-case (`default_dark` → `default-dark`).

**MUST NOT** embed raw `#hex` in `colors` — palette refs only ([palette-ref-grammar](../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/palette-ref-grammar.md)).

Loaded by `BuiltinThemeCatalog` → `ThemeYamlNormalizer` → `ThemeConfig` / `ThemeTokenResolver`.
