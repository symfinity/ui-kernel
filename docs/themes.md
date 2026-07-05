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

`CssGenerator` emits `[data-ui-physics="…"]` blocks with `--ui-physics-*` tokens and bridge aliases to `--ui-motion-*` / `--ui-radius-*`. Complements `data-ui-surface="glass"` on components (component blur) — not a merge.

## Layout profiles

Built-in themes map to one of two rhythm profiles:

| Profile | Spacing | Typography | Lineages |
|---------|---------|------------|----------|
| **Semantic** | Roomy | 1rem base | `default`, `semantic` pairs |
| **Utility** | Compact | 0.875rem base | `utility` pair |

Radius, shadow, and motion character follow the active **physics** profile (default `flat`). DTCG theme layers still define per-lineage appearance tokens until authors opt into non-flat physics via `data-ui-physics`.

## Theme engine milestones (0.3.x) {#theme-engine-milestones}

Public **0.3.x** line — runtime switching, overlays, and documented capability ids. Theme YAML stays on `schema_version: '2.0'` until a later release explicitly versions it.

| Theme-engine capability | v0.3.0 | v0.3.1 | Later 0.3.x |
|-------------------------|--------|--------|-------------|
| Runtime switching (boot script, cookies, scheme API) | **v0.3.0** | — | — |
| Theme overlays (`user_tokens`, app DTCG overrides) | **v0.3.0** | — | — |
| Session preview overlay (preview hosts only) | **v0.3.0** | — | — |
| Theme capabilities vocabulary (documented ids) | **v0.3.0** | — | — |
| Runtime capability probe (`ui_kernel_theme_capabilities()`) | **v0.3.0** | — | — |
| Nested themes (`extends` single-hop merge) | — | **v0.3.1** | — |
| Theme inheritance (multi-hop chains) | — | **v0.3.1** | — |
| Theme composition (author graphs) | Boundary documented | — | ui-themer + future kernel hooks |

**v0.3.0** ships `runtime_switch`, `user_token_overlay`, `session_preview_overlay`, and `app_dtcg_override`, plus the runtime probe — see [Theme capabilities](#theme-capabilities-v03). **v0.3.1** adds functional `extends` on variant entries and multi-hop theme-layer merge. Composition graph UI and `schema_version: '3.0'` remain later **0.3.x** work.

## Runtime theme switching {#runtime-theme-switching}

**Capability id:** `runtime_switch` — see [Theme capabilities](#theme-capabilities-v03) and [Theme engine milestones](#theme-engine-milestones).

### Boot script and paint order

Include `ui_kernel_theme_boot_script()` in `<head>` **before** theme CSS so `document.documentElement.dataset.theme` is set before the first paint. Prefer the bundle head partial:

```twig
{% include '@UiKernel/_head.html.twig' %}
```

Manual order when not using the partial:

```twig
{{ ui_kernel_theme_boot_script() }}
{{ ui_kernel_layer_order()|raw }}
{{ ui_kernel_css()|raw }}
```

The inline script reads preference cookies, resolves light/dark variant ids per lineage, and sets `root.dataset.theme` plus `root.style.colorScheme`.

### Preference cookies

| Cookie | Purpose |
|--------|---------|
| `symfinity_ui_kernel_lineage` | Active lineage (`default`, `semantic`, `utility`, …) |
| `symfinity_ui_kernel_scheme` | `auto`, `light`, or `dark` |

Unknown or removed lineage values fall back to `default_theme` from config. Invalid scheme values fall back to `auto`. Cookies are `HttpOnly`, `SameSite=Lax`, one-year TTL.

Set cookies via the scheme API, theme shell presenter, or your own controller using the same keys — do not invent parallel preference storage.

### Scheme API

When `scheme` is `auto`, the boot script may `PATCH` the kernel scheme endpoint (same route as `ui_kernel_theme_shell()`) with optional `systemPrefersDark` from `matchMedia('(prefers-color-scheme: dark)')` when the Client Hint header is absent. The JSON response carries resolved `themeId`, `colorScheme`, and refreshed CSS for `#ui-kernel-theme-css`.

Without a scheme endpoint, `auto` resolves client-side from `prefers-color-scheme` using the configured lineage pair (`semantic` / `semantic-dark`, etc.).

### Adaptive pairs

Each built-in lineage exposes a light/dark variant pair. With `scheme: auto` and no stored cookie override, the active variant follows OS dark preference for that lineage.

### Unknown variant ids

Markup or query `?theme=` with an unknown id throws in PHP paths that validate against `ThemeRegistry`. The boot script falls back to the config default variant id when lineage resolution fails. After upgrades, stale cookies for removed lineages reset to the configured default lineage.

## Theme capabilities (v0.3) {#theme-capabilities-v03}

Shared vocabulary for apps, dogfood labs, and optional packages:

| Capability id | Meaning | Consumer entry point |
|---------------|---------|----------------------|
| `runtime_switch` | User or OS changes variant without redeploy | Boot script, cookies, scheme API, `ui_kernel_theme_shell()` |
| `user_token_overlay` | Config `user_tokens` merge atop registry theme | `symfinity_ui_kernel.user_tokens` in YAML |
| `session_preview_overlay` | Ephemeral author draft CSS on preview hosts | `SessionThemeInjectionPort` (ui-themer implements) |
| `app_dtcg_override` | App `config/themes/{lineage}/` overrides bundle | `themes_directory` + merged `ThemeRegistry` |
| `nested_theme` | Child variant resolves through parent theme layer | `extends` on variant entry in `theme.meta.yaml` |
| `inheritance_chain` | Multi-hop parent merge (depth limit configurable) | Same as nested; probe true when chain length ≥ 2 hops |

**Runtime probe:** `ThemeCapabilityProbe::enabled()` and `ui_kernel_theme_capabilities()` report which capability ids are active in the current app — see [reference.md](reference.md#twig-helpers).

**Consumer checklist**

1. Call `ui_kernel_layer_order()` before theme CSS (**0.2.4+**).
2. Include `ui_kernel_theme_boot_script()` in `<head>` when using `runtime_switch`.
3. Document cookie / scheme behaviour for your shell.
4. Use preview injection only on designated preview routes with a valid session token.

## Theme inheritance (v0.3.1) {#theme-inheritance-v031}

**Capability ids:** `nested_theme`, `inheritance_chain` — see [Theme capabilities](#theme-capabilities-v03).

Add optional `extends` on a variant entry to inherit another registered variant's theme-layer DTCG before applying child overrides:

```yaml
# config/themes/brand-a/theme.meta.yaml
variants:
  - id: brand-a
    layer_file: brand-a.dtcg.yaml
    extends: semantic
```

```yaml
# config/themes/brand-a/brand-a.dtcg.yaml — theme-layer overrides only
color:
  primary:
    $type: color
    $value: '{color.red.600}'
```

Rules:

- `extends` references a **variant id** registered in the merged catalog (built-in or app), not a lineage folder name alone.
- Merge walks the chain root → leaf; each step replaces whole tokens by path (same semantics as layer stack merge).
- Cycles and chains longer than `symfinity_ui_kernel.theme_inheritance.max_depth` fail at catalog load.
- Child themes without overrides inherit the parent theme layer output unchanged.

## Theme overlay merge order {#theme-overlay-merge-order}

### Standard consumer page (no preview host)

```text
1. Resolve `extends` chain → merged theme-role DTCG (when present)
2. Built-in or app registry theme (DTCG stack: base ⊕ design_system ⊕ merged theme)
3. Config user_tokens overrides on the active variant
4. Generated --ui-* CSS in @layer ui-kernel-theme
```

### Preview host with valid session token

```text
1. Registry baseline (built-in ids unchanged)
2. Injected preview CSS from SessionThemeInjectionPort (separate stylesheet or inline block)
3. MUST NOT mutate ThemeRegistry entries
```

When both `user_tokens` and preview injection apply, preview hosts use the session overlay on top of the registry baseline; config `user_tokens` still apply to generated baseline CSS before preview CSS is injected.

## Dark mode and `data-theme`

Pair light/dark ids per lineage (`default` / `default-dark`, etc.). The boot script and scheme API above are the supported switching path — see [Runtime theme switching](#runtime-theme-switching).

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
| Typography `muted` | typography role variant | Helper text |
| Structural `text-muted` | `--ui-color-text-muted` | Foreground token — not `data-ui-variant` |
| `ghost` | `data-ui-appearance="ghost"` on Button/Link only | Transparent hover wash — **not** a semantic colour |

## See also

- [ROADMAP](../ROADMAP.md)
- [Configuration](configuration.md)
- [Quick start](quickstart.md)
