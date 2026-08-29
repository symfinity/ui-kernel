# UI Kernel architecture

Consumer view of **symfinity/ui-kernel** — how theme data becomes CSS and how components attach hooks on the page.

## Spine

| Piece | Role |
|-------|------|
| **UiPage** / **UiComponent** | Transport-agnostic UI tree (Symfony UI Kernel RFC) |
| **HtmlRenderer** | Web HTML with `data-ui-role`, `data-ui-variant`, `data-ui-fragment` |
| **ThemeRegistry** + **CssGenerator** | Resolve theme YAML and emit runtime token CSS |

Theme YAML and generator settings ship inside the bundle. Application config should override wiring only (`default_theme`, `user_tokens`, …) — see [Configuration](configuration.md).

## Output channels

v0 targets **web HTML** only. Email, CLI, or alternate renderers are out of scope for this package; pair UX Blocks tiers when you need component markup on the web channel.

## Baseline themes

Built-in lineages include **semantic** (default) and dark variants. Token math uses OKLCH ramps — details in [Themes](themes.md) and [DTCG token core](dtcg-token-core.md).

## Pair with UX Blocks

UI Kernel supplies **tokens and shared primitives** (form controls, scroll, overlays). Component-specific `[data-ui-role]` CSS lives in tier packages (`symfinity/ux-blocks-core`, …). Install both when you render ux-blocks components.

## Optional companions

| Package | Purpose |
|---------|---------|
| [symfinity/font-manager](https://packagist.org/packages/symfinity/font-manager) | Webfont pairing — [Font Manager pairing](font-manager-pairing.md) |
| [symfony/web-profiler-bundle](https://symfony.com/doc/current/profiler.html) | WDT theme panel — [Web Profiler](profiler.md) |

## Next steps

- [Quick start](quickstart.md) — boot script + CSS on every page
- [Usage](usage.md) — overrides without forking the bundle
