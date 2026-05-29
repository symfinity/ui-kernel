<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Flavour;

/**
 * SSOT for all built-in theme flavours (ids, labels, colors, layout lineage).
 *
 * @see docs/theme-flavours.md
 */
final class FlavourCatalog
{
    /**
     * @return list<Flavour>
     */
    public static function all(): array
    {
        static $flavours = null;

        if ($flavours === null) {
            $flavours = [];
            foreach (self::definitions() as $definition) {
                $flavours[] = new DefinedFlavour(
                    $definition['id'],
                    $definition['label'],
                    $definition['layout'],
                    $definition['colors'],
                );
            }
        }

        return $flavours;
    }

    public static function get(string $id): Flavour
    {
        foreach (self::all() as $flavour) {
            if ($flavour->id() === $id) {
                return $flavour;
            }
        }

        throw new \InvalidArgumentException(sprintf('Unknown flavour id "%s".', $id));
    }

    /**
     * @return list<array{id: string, label: string, layout: LayoutProfile, colors: array<string, string>}>
     */
    private static function definitions(): array
    {
        return [
            // Kiroshi — Cyberpunk / Night City inspired (https://www.cyberpunk.net/)
            [
                'id' => 'default',
                'label' => 'Kiroshi',
                'layout' => LayoutProfile::Kiroshi,
                'colors' => [
                    '--ui-color-primary' => '#0a0a0a',
                    '--ui-color-secondary' => '#00e5ff',
                    '--ui-color-surface' => '#fcee0a',
                    '--ui-color-surface-elevated' => '#fffef5',
                    '--ui-color-text' => '#0a0a0a',
                    '--ui-color-text-muted' => '#3d3d45',
                    '--ui-color-border' => '#0a0a0a',
                    '--ui-color-danger' => '#ff2a6d',
                    '--ui-color-success' => '#00a86b',
                ],
            ],
            [
                'id' => 'dark',
                'label' => 'Kiroshi dark',
                'layout' => LayoutProfile::Kiroshi,
                'colors' => [
                    '--ui-color-primary' => '#fcee0a',
                    '--ui-color-secondary' => '#00f0ff',
                    '--ui-color-surface' => '#050508',
                    '--ui-color-surface-elevated' => '#0c0c10',
                    '--ui-color-text' => '#ffffff',
                    '--ui-color-text-muted' => '#8b8b9e',
                    '--ui-color-border' => '#2e2e3a',
                    '--ui-color-danger' => '#ff003c',
                    '--ui-color-success' => '#39ff14',
                ],
            ],
            // Semantic lineage (component-library rhythm)
            [
                'id' => 'semantic',
                'label' => 'Semantic',
                'layout' => LayoutProfile::Semantic,
                'colors' => [
                    '--ui-color-primary' => '#0d6efd',
                    '--ui-color-secondary' => '#6c757d',
                    '--ui-color-surface' => '#f8f9fa',
                    '--ui-color-surface-elevated' => '#ffffff',
                    '--ui-color-text' => '#212529',
                    '--ui-color-text-muted' => '#6c757d',
                    '--ui-color-border' => '#dee2e6',
                    '--ui-color-danger' => '#dc3545',
                    '--ui-color-success' => '#198754',
                ],
            ],
            [
                'id' => 'semantic-dark',
                'label' => 'Semantic dark',
                'layout' => LayoutProfile::Semantic,
                'colors' => [
                    '--ui-color-primary' => '#6ea8fe',
                    '--ui-color-secondary' => '#6c757d',
                    '--ui-color-surface' => '#212529',
                    '--ui-color-surface-elevated' => '#2b3035',
                    '--ui-color-text' => '#dee2e6',
                    '--ui-color-text-muted' => '#adb5bd',
                    '--ui-color-border' => '#495057',
                    '--ui-color-danger' => '#ea868f',
                    '--ui-color-success' => '#75b798',
                ],
            ],
            // Utility lineage (utility-scale rhythm)
            [
                'id' => 'utility',
                'label' => 'Utility',
                'layout' => LayoutProfile::Utility,
                'colors' => [
                    '--ui-color-primary' => '#3b82f6',
                    '--ui-color-secondary' => '#64748b',
                    '--ui-color-surface' => '#f8fafc',
                    '--ui-color-surface-elevated' => '#ffffff',
                    '--ui-color-text' => '#0f172a',
                    '--ui-color-text-muted' => '#64748b',
                    '--ui-color-border' => '#e2e8f0',
                    '--ui-color-danger' => '#ef4444',
                    '--ui-color-success' => '#22c55e',
                ],
            ],
            [
                'id' => 'utility-dark',
                'label' => 'Utility dark',
                'layout' => LayoutProfile::Utility,
                'colors' => [
                    '--ui-color-primary' => '#60a5fa',
                    '--ui-color-secondary' => '#94a3b8',
                    '--ui-color-surface' => '#0f172a',
                    '--ui-color-surface-elevated' => '#1e293b',
                    '--ui-color-text' => '#f1f5f9',
                    '--ui-color-text-muted' => '#94a3b8',
                    '--ui-color-border' => '#334155',
                    '--ui-color-danger' => '#f87171',
                    '--ui-color-success' => '#4ade80',
                ],
            ],
        ];
    }
}
