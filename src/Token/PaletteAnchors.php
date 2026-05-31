<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Showcase parity anchors — palette SSOT hex overrides (009).
 *
 * @internal
 */
final class PaletteAnchors
{
    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            // default / Kiroshi
            'mono.warm.950' => '#0a0a0a',
            'mono.warm.0' => '#ffffff',
            'mono.warm.975' => '#050508',
            'cyan.500' => '#00e5ff',
            'cyan.400' => '#00f0ff',
            'mono.warm.100' => '#fcee0a',
            'mono.warm.50' => '#fffef5',
            'mono.warm.700' => '#3d3d45',
            'mono.warm.900' => '#0c0c10',
            'mono.warm.500' => '#8b8b9e',
            'mono.warm.800' => '#2e2e3a',
            'mono.warm.200' => '#fcee0a',
            'red.500' => '#ff2a6d',
            'red.600' => '#ff003c',
            'green.500' => '#00a86b',
            'green.400' => '#39ff14',

            // semantic light
            'blue.600' => '#0d6efd',
            'blue.500' => '#3b82f6',
            'mono.cool.500' => '#6c757d',
            'mono.cool.50' => '#f8f9fa',
            'mono.cool.0' => '#ffffff',
            'mono.cool.900' => '#212529',
            'mono.cool.400' => '#6c757d',
            'mono.cool.200' => '#dee2e6',
            'mono.cool.100' => '#f8f9fc',
            'red.700' => '#dc3545',
            'green.700' => '#198754',

            // semantic dark
            'blue.400' => '#6ea8fe',
            'mono.cool.850' => '#212529',
            'mono.cool.825' => '#2b3035',
            'mono.cool.100' => '#dee2e6',
            'mono.cool.300' => '#adb5bd',
            'mono.cool.600' => '#495057',

            // utility light
            'slate.500' => '#64748b',
            'slate.50' => '#f8fafc',
            'slate.0' => '#ffffff',
            'slate.900' => '#0f172a',
            'slate.400' => '#64748b',
            'slate.200' => '#e2e8f0',
            'red.500u' => '#ef4444',
            'green.500u' => '#22c55e',

            // utility dark
            'blue.400u' => '#60a5fa',
            'slate.400u' => '#94a3b8',
            'slate.950' => '#0f172a',
            'slate.800' => '#1e293b',
            'slate.100' => '#f1f5f9',
            'slate.500u' => '#94a3b8',
            'slate.700' => '#334155',
            'slate.600' => '#475569',
            'red.400u' => '#f87171',
            'green.400u' => '#4ade80',

            // status / derived anchors
            'yellow.500' => '#eab308',
            'blue.500i' => '#0ea5e9',
            'mono.cool.950@40' => 'rgba(15, 23, 42, 0.4)',
            'mono.cool.200s' => '#e9ecef',
            'mono.cool.100s' => '#f8f9fc',
            'red.300' => '#ea868f',
            'green.300' => '#75b798',
        ];
    }
}
