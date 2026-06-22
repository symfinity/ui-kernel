<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Normalizes semantic colour prop values against a graph-derived vocabulary (077 / 115).
 */
final class ColourPropsNormalizer
{
    /** @var array<string, string> legacy alias => graph name */
    private const LEGACY_ALIASES = [
        '' => 'primary',
        'default' => 'primary',
        'destructive' => 'danger',
        'tertiary' => 'accent',
        'ghost' => 'neutral',
    ];

    /** @var list<string> */
    private const APPEARANCE_VARIANT_ALIASES = ['outline', 'link'];

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

        if (\in_array($candidate, self::APPEARANCE_VARIANT_ALIASES, true)) {
            return $this->vocabulary->defaultName();
        }

        if ($this->vocabulary->contains($candidate)) {
            return $candidate;
        }

        return $this->vocabulary->defaultName();
    }

    /**
     * @return array{variant: string, appearance: string}
     */
    public function normalizeButtonColour(string $variant, string $appearance): array
    {
        $candidate = self::LEGACY_ALIASES[$variant] ?? $variant;

        if (\in_array($candidate, self::APPEARANCE_VARIANT_ALIASES, true)) {
            return [
                'variant' => $this->vocabulary->defaultName(),
                'appearance' => $candidate,
            ];
        }

        if ($variant === 'ghost') {
            if ($appearance !== 'solid') {
                return [
                    'variant' => 'neutral',
                    'appearance' => $appearance,
                ];
            }

            return [
                'variant' => 'neutral',
                'appearance' => 'ghost',
            ];
        }

        $normalizedVariant = $this->normalize($variant);

        return [
            'variant' => $normalizedVariant,
            'appearance' => $appearance,
        ];
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
