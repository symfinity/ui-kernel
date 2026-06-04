# Output channels (package index)

Normative contract: output-channels.md.

| Channel | Renderer | 002 |
|---------|----------|-----|
| Web HTML | `HtmlRenderer` | Shipped |
| Web fragment | `HtmlFragmentRenderer` | Deferred → 004 |
| CLI | `CliRenderer` | Horizon |
| JSON | `JsonRenderer` | Horizon |
| Email | `EmailRenderer` | Horizon |
| PDF / print | `PrintRenderer` | Horizon |

`UiPage` stays transport-agnostic; add renderers without forking theme logic.
