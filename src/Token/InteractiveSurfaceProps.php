<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Normalizes legacy combined variant values for Button / Link ({@code variant} + {@code appearance}, 060).
 */
final class InteractiveSurfaceProps
{
    /**
     * @param array<string, mixed> $props
     *
     * @return array<string, mixed>
     */
    public static function normalize(array $props): array
    {
        $variant = \is_scalar($props['variant'] ?? null) ? (string) $props['variant'] : 'primary';
        $appearance = \is_scalar($props['appearance'] ?? null) ? (string) $props['appearance'] : Appearance::DEFAULT;

        if ('outline' === $variant) {
            $variant = 'primary';
            $appearance = Appearance::OUTLINE;
        } elseif ('link' === $variant) {
            $variant = 'primary';
            $appearance = Appearance::LINK;
        } else {
            $variant = SemanticVariant::coerceLegacy($variant);
            if (!SemanticVariant::isValid($variant)) {
                $variant = 'primary';
            }
            if (!Appearance::isValid($appearance)) {
                $appearance = Appearance::DEFAULT;
            }
        }

        $props['variant'] = $variant;
        $props['appearance'] = $appearance;

        return $props;
    }
}
