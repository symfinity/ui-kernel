<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

/**
 * Kernel-maintained physics profiles — radius, elevation, motion character (111).
 *
 * Preset owns typography + spacing only after migration; physics owns material tokens.
 */
final class PhysicsRegistry
{
    /** @var list<string> */
    public const PHYSICS_TOKEN_KEYS = [
        '--ui-physics-id',
        '--ui-physics-radius-md',
        '--ui-physics-radius-lg',
        '--ui-physics-shadow-elevated',
        '--ui-physics-hover-lift',
        '--ui-physics-motion-duration-fast',
        '--ui-physics-motion-duration-normal',
        '--ui-physics-motion-duration-slow',
        '--ui-physics-motion-easing-standard',
        '--ui-physics-motion-easing-emphasis',
    ];

    /** @var list<string> */
    public const PRESET_FORBIDDEN_KEYS = [
        '--ui-radius-xs',
        '--ui-radius-sm',
        '--ui-radius-md',
        '--ui-radius-lg',
        '--ui-radius-full',
        '--ui-shadow-sm',
        '--ui-shadow-md',
        '--ui-shadow-lg',
        '--ui-motion-duration-fast',
        '--ui-motion-duration-normal',
        '--ui-motion-duration-slow',
        '--ui-motion-duration-skeleton',
        '--ui-motion-easing-standard',
        '--ui-motion-easing-linear',
    ];

    /**
     * @return array<string, string>
     */
    public function tokensFor(PhysicsId $id): array
    {
        $profile = match ($id) {
            PhysicsId::Flat => [
                '--ui-physics-radius-md' => '0.375rem',
                '--ui-physics-radius-lg' => '0.5rem',
                '--ui-physics-shadow-elevated' => 'var(--ui-shadow-lg)',
                '--ui-physics-hover-lift' => '0',
                '--ui-physics-motion-duration-fast' => '120ms',
                '--ui-physics-motion-duration-normal' => '130ms',
                '--ui-physics-motion-duration-slow' => '300ms',
                '--ui-physics-motion-easing-standard' => 'cubic-bezier(0, 0, 0.2, 1)',
                '--ui-physics-motion-easing-emphasis' => 'cubic-bezier(0, 0, 0.2, 1)',
            ],
            PhysicsId::Glass => [
                '--ui-physics-radius-md' => '12px',
                '--ui-physics-radius-lg' => '16px',
                '--ui-physics-shadow-elevated' => '0 8px 32px color-mix(in oklch, var(--ui-color-primary) 25%, transparent)',
                '--ui-physics-hover-lift' => '-3px',
                '--ui-physics-motion-duration-fast' => '200ms',
                '--ui-physics-motion-duration-normal' => '300ms',
                '--ui-physics-motion-duration-slow' => '450ms',
                '--ui-physics-motion-easing-standard' => 'cubic-bezier(0.34, 1.56, 0.64, 1)',
                '--ui-physics-motion-easing-emphasis' => 'cubic-bezier(0.34, 1.56, 0.64, 1)',
            ],
            PhysicsId::Retro => [
                '--ui-physics-radius-md' => '0',
                '--ui-physics-radius-lg' => '0',
                '--ui-physics-shadow-elevated' => '3px 3px 0 0 color-mix(in oklch, var(--ui-color-text) 85%, transparent)',
                '--ui-physics-hover-lift' => '-2px',
                '--ui-physics-motion-duration-fast' => '0ms',
                '--ui-physics-motion-duration-normal' => '0ms',
                '--ui-physics-motion-duration-slow' => '0ms',
                '--ui-physics-motion-easing-standard' => 'steps(2, end)',
                '--ui-physics-motion-easing-emphasis' => 'steps(4, end)',
            ],
        };

        $profile['--ui-physics-id'] = $id->value;

        return $profile;
    }

    /**
     * Bridge aliases so role CSS using {@code --ui-motion-*} / {@code --ui-radius-*} picks up physics.
     *
     * @return array<string, string>
     */
    public function bridgeAliases(PhysicsId $id): array
    {
        $bridges = [
            '--ui-radius-md' => 'var(--ui-physics-radius-md)',
            '--ui-radius-lg' => 'var(--ui-physics-radius-lg)',
            '--ui-motion-duration-fast' => 'var(--ui-physics-motion-duration-fast)',
            '--ui-motion-duration-normal' => 'var(--ui-physics-motion-duration-normal)',
            '--ui-motion-duration-slow' => 'var(--ui-physics-motion-duration-slow)',
            '--ui-motion-easing-standard' => 'var(--ui-physics-motion-easing-standard)',
            '--ui-shadow-lg' => 'var(--ui-physics-shadow-elevated)',
        ];

        if ($id === PhysicsId::Retro) {
            $bridges['--ui-radius-xs'] = '0';
            $bridges['--ui-radius-sm'] = '0';
        }

        return $bridges;
    }

    /**
     * Flat physics tokens merged at theme resolve when {@code physics} is omitted (111 alias window).
     *
     * @return array<string, string>
     */
    public function flatResolveTokens(): array
    {
        $tokens = $this->tokensFor(PhysicsId::Flat);
        unset($tokens['--ui-physics-id']);

        return [
            '--ui-radius-xs' => '0.125rem',
            '--ui-radius-sm' => '0.25rem',
            '--ui-radius-md' => $tokens['--ui-physics-radius-md'],
            '--ui-radius-lg' => $tokens['--ui-physics-radius-lg'],
            '--ui-radius-full' => '9999px',
            '--ui-motion-duration-fast' => $tokens['--ui-physics-motion-duration-fast'],
            '--ui-motion-duration-normal' => $tokens['--ui-physics-motion-duration-normal'],
            '--ui-motion-duration-slow' => $tokens['--ui-physics-motion-duration-slow'],
            '--ui-motion-duration-skeleton' => '1.75s',
            '--ui-motion-easing-standard' => $tokens['--ui-physics-motion-easing-standard'],
            '--ui-motion-easing-linear' => 'linear',
            '--ui-shadow-sm' => '0 1px 2px rgba(0, 0, 0, 0.06)',
            '--ui-shadow-md' => '0 4px 12px rgba(0, 0, 0, 0.1)',
            '--ui-shadow-lg' => '0 12px 28px rgba(0, 0, 0, 0.15)',
        ];
    }

    /**
     * Layout tokens owned by physics — merged after Preset typography/spacing (111).
     *
     * @return array<string, string>
     */
    public function appearanceResolveTokens(PhysicsId $id): array
    {
        if ($id === PhysicsId::Flat) {
            return $this->flatResolveTokens();
        }

        $profile = $this->tokensFor($id);
        unset($profile['--ui-physics-id']);

        $tokens = $this->flatResolveTokens();
        $tokens['--ui-radius-md'] = $profile['--ui-physics-radius-md'];
        $tokens['--ui-radius-lg'] = $profile['--ui-physics-radius-lg'];
        $tokens['--ui-motion-duration-fast'] = $profile['--ui-physics-motion-duration-fast'];
        $tokens['--ui-motion-duration-normal'] = $profile['--ui-physics-motion-duration-normal'];
        $tokens['--ui-motion-duration-slow'] = $profile['--ui-physics-motion-duration-slow'];
        $tokens['--ui-motion-easing-standard'] = $profile['--ui-physics-motion-easing-standard'];
        $tokens['--ui-shadow-lg'] = $profile['--ui-physics-shadow-elevated'];

        if ($id === PhysicsId::Retro) {
            $tokens['--ui-radius-xs'] = '0';
            $tokens['--ui-radius-sm'] = '0';
            $tokens['--ui-motion-duration-skeleton'] = '0ms';
            // Validated theme tokens use linear; retro `steps()` easing ships in [data-ui-physics="retro"] CSS.
            $tokens['--ui-motion-easing-standard'] = 'linear';
        }

        return $tokens;
    }
}
