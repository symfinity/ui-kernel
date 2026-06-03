# symfinity/ui-kernel

**Chameleon UI** (marketing) · **UI Kernel** (technical) — typed UI tree, token themes, and theme showcase for Symfony apps.

## Showcase (dev)

With **UiKernelBundle** and **SymfinityUxBlocksCoreBundle** registered (Chameleon default):

```text
GET /ui-kernel/showcase
GET /ui-kernel/showcase?theme=dark
GET /ui-kernel/showcase?carousel=0
GET /_ui/theme.css?theme=bootstrap-like
```

Gallery slots render **symfinity/ux-blocks-core** Twig components (`blocks.*` fragments). Theme rotation exercises kernel token CSS only — gallery markup stays fixed.

Themes are **Symfinity token packs inspired by** common systems — not official Bootstrap or Tailwind. Baseline labels: **Kiroshi** (`default`), **Kiroshi dark** (`dark`).

## QA

From the product monorepo root (`src/symfinity/`):

```bash
./sbin/php vendor/bin/mono package:validate packages/ui-kernel/
./sbin/php vendor/bin/mono qa:test
```

Package-only:

```bash
cd packages/ui-kernel && composer install && composer test
```

## Optional copy

```bash
composer require symfinity/ux-blocks-core symfinity/omnia-ipsum
```

Showcase gallery requires **symfinity/ux-blocks-core** (+ Symfony UX twig-component / stimulus-bundle). Dogfood preset `ui-kernel-showcase` wires this via [symfinity-ui-kernel overlay](../../../../bin/dogfood/overlays/symfinity-ui-kernel.json).

## Primal lab reference (WoWi)

Source: [`var/primal/td-cc-wowi`](../../../../var/primal/td-cc-wowi) (reference only).

| WoWi pattern | Notes for UI Kernel |
|--------------|---------------------|
| `scaleMagic` — viewport-height sections minus fixed header/subnav | Layout tokens + scroll-offset hooks for full-bleed heroes |
| Scroll-spy section tracking (Tealium) | Host analytics only — kernel supplies stable section markup hooks |

## Port reference

Read-only legacy source may be staged under `_archive/import-packages/kiroshi-ui/` per [agent-local-staging](../../../../docs-classified/guidelines/agent-local-staging.md). Do not treat archive paths as product canon.

## Native overlay hooks (**016**)

Kernel-generated CSS (schema `2.0`) styles native top-layer UI without Stimulus in this package:

| Pattern | Markup hooks | Contract |
|---------|--------------|----------|
| Modal | `<dialog data-ui-role="modal" class="ui-dialog">` | [native-overlay-css](../../../../specs/symfinity/symfinity/16-ui-kernel-final-css/contracts/native-overlay-css.md) |
| Popover | `[popover][data-ui-role="popover"].ui-popover` | same |
| Anchored menu | Trigger `data-ui-anchor="trigger"`; panel `data-ui-role="menu"` | `@supports` anchor + absolute fallback |
| Skeleton / defer / `:has()` | `data-ui-role="skeleton"`, `data-ui-defer="cv"`, `data-ui-role="field-group"` | [scroll-and-loading-css](../../../../specs/symfinity/symfinity/16-ui-kernel-final-css/contracts/scroll-and-loading-css.md) |

Twig macros and `commandfor` live in **ux-blocks** — do not duplicate overlay colours in blocks CSS; consume `/_ui/theme.css`.

Browsers without `popover` / anchor support still get fallback layout from generated `@supports` blocks; no kernel polyfill scripts.

## Planning

- [symfinity 002 — ui-kernel](../../../../specs/symfinity/symfinity/2-ui-kernel/spec.md)
- [symfinity 016 — ui-kernel-final-css](../../../../specs/symfinity/symfinity/16-ui-kernel-final-css/spec.md)
- [Symfony UI Kernel RFC](../../../../docs-classified/rfc/symfony_ui_kernel_rfc.md)
