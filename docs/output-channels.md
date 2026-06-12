# Output channels (package index)

Normative contract: [output-channels.md](../../../../specs/symfinity/symfinity/2-ui-kernel/contracts/output-channels.md).

**002 shipped:** kernel **CSS** via `ui_kernel_css()` and theme token engine.

| Channel | Owner | Status |
|---------|-------|--------|
| Web HTML markup | `symfinity/ux-blocks-*` Twig components | Shipped (catalog) |
| Web theme CSS | `symfinity/ui-kernel` `CssGenerator` | Shipped |
| Web fragment / Turbo | `symfinity/ux-runtime` | Optional |
| CLI / JSON / Email / PDF | Horizon | Not in kernel |

Multi-channel `UiPage` trees are **not** part of `ui-kernel`; see `ux-runtime` for action response DTOs.
