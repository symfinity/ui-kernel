# DTCG profile globals and graph consumers

Platform-wide structural tokens and a read-only variant catalog ship alongside built-in themes.

## Profile globals layer

- Token file: `config/tokens/profile-globals.dtcg.yaml` (JSON twin for parity tooling).
- Loaded via `ProfileGlobalsLayerRegistry` — **not** merged into per-theme `DesignTokenSet` (z-index and keyframes stay globals-only).
- Cache key fragment includes the profile-globals revision so CSS invalidates when globals change.

## Emission

- `AtRulesContributorInterface` / `AtRulesContributor` — `:root` z-index vars and `@keyframes` from DTCG extensions.
- `CssGenerator` delegates profile globals; no inline `@keyframes` or z-index literals remain in the generator.
- Reduced-motion wrapper for `ui-shimmer` respects the configured system profile.

## Graph variant catalog port

- `GraphVariantCatalogPort` → default `GraphVariantCatalog` (semantic colour slugs from the active built-in theme graph).
- Optional consumers: apps that need variant pickers in catalog UIs, and the kernel baseline Web Debug Toolbar collector.

Integrators can depend on the port interface to list allowed `data-ui-variant` slugs without hard-coding colour names.

## See also

- [DTCG token core](dtcg-token-core.md)
- [Themes](themes.md)
- [Configuration](configuration.md) — `system_profile` overrides
