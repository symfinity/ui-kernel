# DTCG token core

The kernel models design tokens internally with the
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
  Depends on no Symfony/Twig/HTTP component, so it can later be extracted as a standalone contracts package.
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

## Related topics

- [Themes](themes.md) — built-in DTCG on-disk layout and `design_system_id`
- [DTCG profile globals and graph consumers](dtcg-globals-consumers.md) — z-index ladder, `@keyframes`, variant catalog
- [Configuration](configuration.md) — app wiring and palette generator SSOT
