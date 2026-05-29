# symfinity/ui-kernel

**Chameleon UI** (marketing) · **UI Kernel** (technical) — typed UI tree, token flavours, and theme showcase for Symfony apps.

## Showcase (dev)

With the bundle registered in a Symfony app:

```text
GET /ui-kernel/showcase
GET /ui-kernel/showcase?theme=dark
GET /ui-kernel/showcase?carousel=0
GET /_ui/theme.css?theme=bootstrap-like
```

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
composer require symfinity/omnia-ipsum
```

Suggested for placeholder lorem in demos — not required by the kernel.

## Port reference

Read-only legacy source may be staged under `_archive/import-packages/kiroshi-ui/` per [agent-local-staging](../../../../docs-classified/guidelines/agent-local-staging.md). Do not treat archive paths as product canon.

## Planning

- [symfinity 002 — ui-kernel](../../../../specs/symfinity/symfinity/2-ui-kernel/spec.md)
- [Symfony UI Kernel RFC](../../../../docs-classified/rfc/symfony_ui_kernel_rfc.md)
