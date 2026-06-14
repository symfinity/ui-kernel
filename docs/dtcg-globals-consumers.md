# DTCG profile globals and graph consumers (078)

**Status:** shipped (**078** `DONE` 2026-06-14)

## Profile globals layer

- Token file: `config/tokens/profile-globals.dtcg.yaml` (+ JSON twin for 076 parity gate).
- Loaded via `ProfileGlobalsLayerRegistry` — **not** merged into theme `DesignTokenSet` (z-index/keyframes stay globals-only).
- Cache key fragment: `CssCacheKeyPolicy::profileGlobalsRevision()` (config path: `symfinity_ui_kernel.dtcg.profile_globals_layer`).

## Emission

- `AtRulesContributorInterface` / `AtRulesContributor` — `:root` z-index vars + `@keyframes` from DTCG extensions.
- `CssGenerator` delegates profile globals; no inline `@keyframes` or z-index literals remain in `CssGenerator.php`.
- Reduced-motion wrapper for `ui-shimmer` per [system-profile contract](../../../specs/symfinity/symfinity/2-ui-kernel/contracts/system-profile.md).

## Graph variant catalog port

- `GraphVariantCatalogPort` → default `GraphVariantCatalog` (semantic colour slugs from active built-in theme graph).
- Consumers: `ux-workshop` `CatalogPropControlBuilder` (variant selects), `ui-profiler` + kernel baseline WDT collectors.

## Contracts

- [profile-globals-tokens](../../../specs/symfinity/symfinity/2-ui-kernel/contracts/profile-globals-tokens.md)
- [at-rules-contributor](../../../specs/symfinity/symfinity/2-ui-kernel/contracts/at-rules-contributor.md)
- [graph-variant-catalog-port](../../../specs/symfinity/symfinity/2-ui-kernel/contracts/graph-variant-catalog-port.md)
- [role-css-eviction-gate](../../../specs/symfinity/symfinity/2-ui-kernel/contracts/role-css-eviction-gate.md)
- [workshop graph consumer](../../../specs/symfinity/symfinity/_org/contracts/ux-workshop/workshop-graph-consumer.md)
- [profiler graph consumer](../../../specs/symfinity/symfinity/_org/contracts/ui-profiler/profiler-graph-consumer.md)
