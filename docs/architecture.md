# UI Kernel architecture (v0)

**Package:** `symfinity/ui-kernel`

## Spine

- **UiPage** / **UiComponent** — transport-agnostic UI tree
- **HtmlRenderer** — web HTML with `data-ui-role`, `data-ui-variant`, `data-ui-fragment`
- **ThemeRegistry** + **CssGenerator** — runtime token CSS; theme data details: [themes.md](./themes.md)

## Output channels

Web HTML only in v0. See [output-channels.md](./output-channels.md).

## Themes

Eight built-in themes ship as `config/themes/{id}.yaml` (e.g. **Kiroshi** `default` / `dark`, **Semantic**, **Utility**). Palette contract and preset recipes live in [configuration.md](./configuration.md). Resolved tokens: [themes.md](./themes.md).

## Related packages

- **symfinity/ui-action** — native action validation
- **symfinity/ux-blocks-core** — Twig components for showcase and apps
