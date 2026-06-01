<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use Symfinity\UiKernel\Flavour\LayoutProfile;

/**
 * Built-in flavour definitions — palette refs only (no hex).
 */
final class FlavourThemeConfig
{
    /**
     * @param array<string, string> $colorRefs semantic role => palette ref
     */
    public function __construct(
        private readonly string $id,
        private readonly string $label,
        private readonly LayoutProfile $layout,
        private readonly MonoSpice $spice,
        private readonly array $colorRefs,
        private readonly string $schemaVersion = ThemeTokenSchema::V2_0,
        private readonly bool $scrollMotion = false,
        private readonly string $backdropBlur = '0',
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function layout(): LayoutProfile
    {
        return $this->layout;
    }

    public function spice(): MonoSpice
    {
        return $this->spice;
    }

    public function schemaVersion(): string
    {
        return $this->schemaVersion;
    }

    /**
     * @return array<string, string>
     */
    public function colorRefs(): array
    {
        return $this->colorRefs;
    }

    public function scrollMotion(): bool
    {
        return $this->scrollMotion;
    }

    public function backdropBlur(): string
    {
        return $this->backdropBlur;
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        return array_map(
            static fn (array $definition): self => new self(
                $definition['id'],
                $definition['label'],
                $definition['layout'],
                $definition['spice'],
                $definition['colors'],
                $definition['schemaVersion'] ?? ThemeTokenSchema::V2_0,
                $definition['scrollMotion'] ?? false,
                $definition['backdropBlur'] ?? '0',
            ),
            self::definitions(),
        );
    }

    public static function get(string $id): self
    {
        foreach (self::all() as $config) {
            if ($config->id() === $id) {
                return $config;
            }
        }

        throw new \InvalidArgumentException(sprintf('Unknown flavour id "%s".', $id));
    }

    /**
     * @return list<array{id: string, label: string, layout: LayoutProfile, spice: MonoSpice, colors: array<string, string>, schemaVersion?: string}>
     */
    private static function definitions(): array
    {
        return [
            [
                'id' => 'default',
                'label' => 'Kiroshi',
                'layout' => LayoutProfile::Kiroshi,
                'spice' => MonoSpice::Warm,
                'colors' => self::kiroshiLight(),
            ],
            [
                'id' => 'dark',
                'label' => 'Kiroshi dark',
                'layout' => LayoutProfile::Kiroshi,
                'spice' => MonoSpice::Warm,
                'colors' => self::kiroshiDark(),
            ],
            [
                'id' => 'semantic',
                'label' => 'Semantic',
                'layout' => LayoutProfile::Semantic,
                'spice' => MonoSpice::Cool,
                'colors' => self::semanticLight(),
                'scrollMotion' => true,
                'backdropBlur' => '6px',
            ],
            [
                'id' => 'semantic-dark',
                'label' => 'Semantic dark',
                'layout' => LayoutProfile::Semantic,
                'spice' => MonoSpice::Cool,
                'colors' => self::semanticDark(),
                'scrollMotion' => true,
                'backdropBlur' => '6px',
            ],
            [
                'id' => 'utility',
                'label' => 'Utility',
                'layout' => LayoutProfile::Utility,
                'spice' => MonoSpice::Cool,
                'colors' => self::utilityLight(),
            ],
            [
                'id' => 'utility-dark',
                'label' => 'Utility dark',
                'layout' => LayoutProfile::Utility,
                'spice' => MonoSpice::Cool,
                'colors' => self::utilityDark(),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function kiroshiLight(): array
    {
        return [
            'primary' => 'mono.warm.950',
            'secondary' => 'cyan.500',
            'tertiary' => 'mono.warm.700',
            'surface' => 'mono.warm.100',
            'surface_elevated' => 'mono.warm.50',
            'text' => 'mono.warm.950',
            'text_muted' => 'mono.warm.700',
            'border' => 'mono.warm.950',
            'danger' => 'red.500',
            'success' => 'green.500',
            'warning' => 'yellow.500',
            'info' => 'cyan.500',
            'focus' => 'cyan.500',
            'overlay' => 'mono.cool.950@40',
            'skeleton_base' => 'mono.cool.200s',
            'skeleton_shine' => 'mono.cool.100s',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function kiroshiDark(): array
    {
        return [
            'primary' => 'mono.warm.200',
            'secondary' => 'cyan.400',
            'tertiary' => 'mono.warm.500',
            'surface' => 'mono.warm.975',
            'surface_elevated' => 'mono.warm.900',
            'text' => 'mono.warm.0',
            'text_muted' => 'mono.warm.500',
            'border' => 'mono.warm.800',
            'danger' => 'red.600',
            'success' => 'green.400',
            'warning' => 'yellow.500',
            'info' => 'cyan.400',
            'focus' => 'cyan.400',
            'overlay' => 'mono.cool.950@40',
            'skeleton_base' => 'mono.warm.800',
            'skeleton_shine' => 'mono.warm.700',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function semanticLight(): array
    {
        return [
            'primary' => 'blue.600',
            'secondary' => 'mono.cool.500',
            'tertiary' => 'mono.cool.400',
            'surface' => 'mono.cool.50',
            'surface_elevated' => 'mono.cool.0',
            'text' => 'mono.cool.900',
            'text_muted' => 'mono.cool.400',
            'border' => 'mono.cool.200',
            'danger' => 'red.700',
            'success' => 'green.700',
            'warning' => 'yellow.500',
            'info' => 'blue.500i',
            'focus' => 'blue.600',
            'overlay' => 'mono.cool.950@40',
            'skeleton_base' => 'mono.cool.200',
            'skeleton_shine' => 'mono.cool.100s',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function semanticDark(): array
    {
        return [
            'primary' => 'blue.400',
            'secondary' => 'mono.cool.300',
            'tertiary' => 'mono.cool.500',
            'surface' => 'mono.cool.850',
            'surface_elevated' => 'mono.cool.825',
            'text' => 'mono.cool.100',
            'text_muted' => 'mono.cool.300',
            'border' => 'mono.cool.600',
            'danger' => 'red.300',
            'success' => 'green.300',
            'warning' => 'yellow.500',
            'info' => 'blue.400',
            'focus' => 'blue.400',
            'overlay' => 'mono.cool.950@40',
            'skeleton_base' => 'mono.cool.600',
            'skeleton_shine' => 'mono.cool.500',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function utilityLight(): array
    {
        return [
            'primary' => 'blue.500',
            'secondary' => 'slate.500',
            'tertiary' => 'slate.400',
            'surface' => 'slate.50',
            'surface_elevated' => 'slate.0',
            'text' => 'slate.900',
            'text_muted' => 'slate.400',
            'border' => 'slate.200',
            'danger' => 'red.500u',
            'success' => 'green.500u',
            'warning' => 'yellow.500',
            'info' => 'blue.500',
            'focus' => 'blue.500',
            'overlay' => 'mono.cool.950@40',
            'skeleton_base' => 'slate.200',
            'skeleton_shine' => 'slate.50',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function utilityDark(): array
    {
        return [
            'primary' => 'blue.400u',
            'secondary' => 'slate.400u',
            'tertiary' => 'slate.500u',
            'surface' => 'slate.950',
            'surface_elevated' => 'slate.800',
            'text' => 'slate.100',
            'text_muted' => 'slate.500u',
            'border' => 'slate.700',
            'danger' => 'red.400u',
            'success' => 'green.400u',
            'warning' => 'yellow.500',
            'info' => 'blue.400u',
            'focus' => 'blue.400u',
            'overlay' => 'mono.cool.950@40',
            'skeleton_base' => 'slate.700',
            'skeleton_shine' => 'slate.600',
        ];
    }
}
