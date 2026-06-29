# Console commands

UI Kernel does not register standalone Symfony console commands. Theme and CSS behaviour is driven by configuration (`config/packages/symfinity_ui_kernel.yaml`, DTCG theme files) and the runtime `CssGenerator`.

| Need | Where |
|------|--------|
| Import or author theme packs | `symfinity/ui-themer` (`ui-themer:import`, Studio UI) |
| Clear generated theme CSS in dev | `bin/console cache:clear` after theme file edits |
| Component role CSS | `symfinity/ux-blocks-*` tier packages (kernel emits tokens only) |

See [configuration.md](configuration.md) and [usage.md](usage.md) for kernel options that affect emitted CSS.
