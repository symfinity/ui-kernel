<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Normalizes semantic colour prop values against a graph-derived vocabulary (077).
 */
final class ColourPropsNormalizer
{
    /** @var array<string, string> legacy alias => graph name */
    private const LEGACY_ALIASES = [
        '' => 'primary',
        'default' => 'primary',
        'destructive' => 'danger',
    ];

    public function __construct(
        private readonly SemanticColourVocabulary $vocabulary,
    ) {
    }

    public static function withBuiltInTheme(string $themeId = 'semantic'): self
    {
        return new self(SemanticColourVocabulary::fromBuiltInThemeId($themeId));
    }

    public function normalize(string $value): string
    {
        $candidate = self::LEGACY_ALIASES[$value] ?? $value;

        if ($this->vocabulary->contains($candidate)) {
            return $candidate;
        }

        return $this->vocabulary->defaultName();
    }

    /**
     * @param array<string, mixed> $props
     *
     * @return array<string, mixed>
     */
    public function normalizeColourProps(array $props, string ...$propNames): array
    {
        $normalized = $props;

        foreach ($propNames as $name) {
            if (!\array_key_exists($name, $normalized) || !\is_scalar($normalized[$name])) {
                continue;
            }

            $normalized[$name] = $this->normalize((string) $normalized[$name]);
        }

        return $normalized;
    }

    public static function tokenKey(string $variant): string
    {
        if ($variant === 'ghost') {
            return '--ui-color-text-muted';
        }

        return '--ui-color-' . $variant;
    }

    /**
     * @return array<string, string> variant => CSS custom property
     */
    public function tokenMap(): array
    {
        $map = [];
        foreach ($this->vocabulary->all() as $variant) {
            $map[$variant] = self::tokenKey($variant);
        }

        return $map;
    }
}
