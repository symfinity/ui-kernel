# UI Kernel architecture (v0)

**Package:** `symfinity/ui-kernel`

## Spine

- **ThemeRegistry** + **CssGenerator** — runtime **token** CSS (`--ui-*`), profile globals (`--ui-z-*`, shared `@keyframes`)
- **UiKernelExtension** — `ui_kernel_css()`, theme shell / boot script Twig helpers
- **Semantic attribute contracts** — `data-ui-role`, `data-ui-variant`, `data-ui-fragment`, `data-theme` (documented; markup owned by `symfinity/ux-blocks-*`)
- **Role presentation CSS** — owned by `symfinity/ux-blocks-*` per [package-role-css-ownership](../../../../specs/symfinity/symfinity/3-ux-component-catalog/contracts/package-role-css-ownership.md); load order: [package-css-cascade](../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/package-css-cascade.md)

## Themes

Six built-in themes ship as `config/themes/{lineage}.yaml` (**Balanced** `default` / `default-dark`, **Semantic**, **Utility**). Palette contract and preset recipes live in [configuration.md](./configuration.md). Resolved tokens: [themes.md](./themes.md).

## Related packages

- **symfinity/ux-blocks-core** — Twig components and tier role CSS
- **symfinity/ui-action** — native action validation
- **symfinity/ux-runtime** — optional Turbo / `UiAction` transport
- **symfinity/ux-blocks-demo** — `/kernel` and `/palette` browser galleries
