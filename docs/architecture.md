# UI Kernel architecture (v0)

**Package:** `symfinity/ui-kernel` · **Planning:** [symfinity 002 — ui-kernel](../../../../specs/symfinity/symfinity/2-ui-kernel/spec.md)

## Spine

- **UiPage** / **UiComponent** — transport-agnostic tree ([RFC](../../../../docs-classified/rfc/symfony_ui_kernel_rfc.md))
- **HtmlRenderer** — web HTML with `data-ui-role`, `data-ui-variant`, `data-ui-fragment`
- **FlavourRegistry** + **CssGenerator** — runtime token CSS ([css-generation](../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/css-generation.md)); flavour data SSOT: [theme-flavours.md](./theme-flavours.md)

## Output channels

Web HTML only in v0. See [output-channels.md](./output-channels.md) and [output-channels contract](../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/output-channels.md).

## Port lineage

Baseline flavours **Kiroshi** / **Kiroshi dark** (`default` / `dark`) follow WebUI lineage per [baseline-flavours](../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/baseline-flavours.md). Maintainer port source: [agent-local-staging](../../../../docs-classified/guidelines/agent-local-staging.md) — not committed paths.

## Deferred

- UiAction / Turbo ([symfinity 004](../../../../specs/symfinity/symfinity/4-ui-action-protocol/spec.md))
- Full UX catalog ([symfinity 003](../../../../specs/symfinity/symfinity/3-ux-component-catalog/spec.md))
