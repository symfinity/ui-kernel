# DTCG token core

Since the DTCG re-core baseline (`0.1.0`), the kernel models design tokens internally with the
[W3C Design Tokens (DTCG) format](https://www.designtokens.org/) and resolves them through a
layered resolver. The public CSS-generation API is unchanged and built-in themes emit equivalent
CSS — the change is in how tokens are modelled and resolved, not in the values produced.

## Concepts

| Term | Meaning |
|------|---------|
| **Token** | A named value with `$value` and `$type` (DTCG). Addressed by a dotted path, e.g. `color.primary`. |
| **Alias** | A `$value` of the form `{group.token}` that references another token. |
| **Layer** | A token source with a role: `base`, `design_system`, or `theme`. |
| **Layer stack** | `base ⊕ design_system ⊕ theme`, merged by precedence (theme wins; whole-token replace by path). |
| **Resolved graph** | The merged, alias-resolved token set — the single input to CSS emission. |

## Namespaces

- `Symfinity\UiKernel\Contract\` — dependency-free SPI (token model, layers, resolver, exceptions).
  Depends on no Symfony/Twig/HTTP component, so it can later be extracted as `ui-kernel-contracts`.
- `Symfinity\UiKernel\Dtcg\` — concrete model, JSON/YAML readers, and the `LayeredTokenResolver`.
- `Symfinity\UiKernel\Css\CssVariableSet` + `CssGenerator::forResolvedGraph()` — emit `--ui-*` from
  a resolved graph (path `color.primary` maps to `--ui-color-primary`).

## Authoring tokens (JSON or YAML)

```yaml
color:
  $type: color
  blue:
    "600": { $value: { colorSpace: oklch, components: [0.55, 0.21, 256] } }
  primary: { $value: "{color.blue.600}" }
```

The same set authored as JSON produces an identical resolved graph. Adding a semantic colour token
(e.g. `color.accent`) in a design-system or theme layer surfaces `--ui-color-accent` and a new
variant with no kernel code change.

## Resolution errors

Resolution never emits broken or empty values. It raises located errors that name the offending
token path:

- `UnresolvableAliasException` — alias target absent from the merged graph.
- `ReferenceCycleException` — alias cycle (`a → b → a`).
- `TokenTypeMismatchException` — alias resolves to an incompatible `$type`.

## Contracts

- [DTCG token model](../../../../specs/symfinity/symfinity/076-ui-kernel-dtcg-token-core/contracts/dtcg-token-model.md)
- [Layered resolver](../../../../specs/symfinity/symfinity/076-ui-kernel-dtcg-token-core/contracts/layered-resolver.md)
- [CSS emitter parity](../../../../specs/symfinity/symfinity/076-ui-kernel-dtcg-token-core/contracts/css-emitter-parity.md)
- [Contract/ namespace boundary](../../../../specs/symfinity/symfinity/076-ui-kernel-dtcg-token-core/contracts/contract-namespace-boundary.md)

## Scope and follow-on

This baseline delivers the resolution core behind the existing API with parity. Deferred to
follow-on features:

- **077** — migrate theme YAML off the bespoke `semantics:/preset/tone` schema, remove the
  `SemanticVariant` enum (graph-authoritative variants), generator-native DTCG emission.
- **078** — move hardcoded globals (`@keyframes`, `--ui-z-*`) to tokens + an at-rules contributor,
  finish the role-CSS eviction tail, and `ux-workshop` / `ui-profiler` graph discovery.
