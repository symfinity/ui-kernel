# Font Manager pairing (optional)

**symfinity/ui-kernel** does **not** require **symfinity/font-manager**. Generated CSS always defines:

- `--ui-font-family-sans`
- `--ui-font-family-mono`

Values use system stacks until you wire font-manager.

## When font-manager is installed

1. `composer require symfinity/font-manager` (suggested in `composer.json`)
2. Configure font-manager to export CSS variable names matching kernel tokens (`--ui-font-family-sans`, `--ui-font-family-mono`)
3. Let font-manager inject `@font-face` rules or link tags; kernel continues to emit the same token names in theme CSS

**Do not** rename tokens between packages.

## When font-manager is absent

Applications remain legible with system stacks — ui-kernel does not ship `@font-face` rules.

## Non-goals (v0.1)

- Runtime auto-detect of font-manager
- Flex recipe requiring font-manager
- Bundled font files inside ui-kernel

## See also

- [Themes](themes.md)
- [Configuration](configuration.md)
