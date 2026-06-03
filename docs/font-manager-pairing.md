# Font-manager pairing (optional)

Normative contract: [font-manager-pairing](../../../../specs/symfinity/symfinity/9-ui-kernel-theme-tokens/contracts/font-manager-pairing.md).

## Kernel default

`symfinity/ui-kernel` **does not** require `symfinity/font-manager`. Generated CSS always emits:

- `--ui-font-family-sans`
- `--ui-font-family-mono`

Values come from [PresetRegistry](../src/Token/PresetRegistry.php) — **system stacks only** (see [typography contract](../../../../specs/symfinity/symfinity/9-ui-kernel-theme-tokens/contracts/typography.md)).

## When font-manager is installed

1. Add suggest package: `composer suggest symfinity/font-manager` (already in `composer.json`).
2. Configure font-manager to export CSS variable names matching kernel tokens (same `--ui-font-family-*` keys).
3. Let font-manager inject `@font-face` / link tags; kernel continues to emit stack names in theme CSS.

**MUST NOT** fork token names between packages.

## When font-manager is absent

Dogfood `/kernel` and consumer apps **MUST** remain legible with system stacks — no kernel `@font-face`.

## Non-goals (v1)

- Runtime auto-detect of font-manager
- Flex recipe requiring font-manager
- font-manager port (see [font-manager-intake](../../../../specs/symfinity/symfinity/intake/font-manager-intake.md))
