<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

final class ThemePreferenceResolver
{
    public function __construct(
        private readonly ThemeRegistry $themeRegistry,
        private readonly string $defaultLineage,
    ) {
    }

    public function defaultLineage(): string
    {
        if (in_array($this->defaultLineage, ThemeLineageCatalog::lineages(), true)) {
            return $this->defaultLineage;
        }

        return ThemeLineageCatalog::lineages()[0] ?? 'default';
    }

    public function preferenceFromThemeId(string $themeId): ThemePreference
    {
        $this->assertKnownThemeId($themeId);

        $lineage = ThemeLineageCatalog::lineageForThemeId($themeId);
        $scheme = ThemeLineageCatalog::isDarkThemeId($themeId)
            ? ThemeColorScheme::Dark
            : ThemeColorScheme::Light;

        return new ThemePreference($lineage, $scheme);
    }

    public function resolveThemeId(ThemePreference $preference, bool $systemPrefersDark): string
    {
        $pair = ThemeLineageCatalog::pairForLineage($preference->lineage);

        return match ($preference->scheme) {
            ThemeColorScheme::Light => $pair['light'],
            ThemeColorScheme::Dark => $pair['dark'],
            ThemeColorScheme::Auto => $systemPrefersDark ? $pair['dark'] : $pair['light'],
        };
    }

    public function resolveTheme(ThemePreference $preference, bool $systemPrefersDark): Theme
    {
        return $this->themeRegistry->get($this->resolveThemeId($preference, $systemPrefersDark));
    }

    public function systemPrefersDark(Request $request): bool
    {
        $hint = strtolower((string) $request->headers->get('Sec-CH-Prefers-Color-Scheme', ''));

        return $hint === 'dark';
    }

    /**
     * Resolves OS dark preference for scheme=auto: Client Hint header first, then optional
     * JSON body `systemPrefersDark` from boot script / SchemeSwitch when the header is absent.
     */
    public function resolveSystemPrefersDark(Request $request): bool
    {
        $hint = strtolower((string) $request->headers->get('Sec-CH-Prefers-Color-Scheme', ''));
        if ($hint === 'dark') {
            return true;
        }
        if ($hint === 'light') {
            return false;
        }

        if ($request->getContent() !== '') {
            /** @var mixed $decoded */
            $decoded = json_decode($request->getContent(), true);
            if (is_array($decoded)
                && array_key_exists('systemPrefersDark', $decoded)
                && is_bool($decoded['systemPrefersDark'])) {
                return $decoded['systemPrefersDark'];
            }
        }

        return false;
    }

    public function applyQueryOverrides(Request $request, ThemePreference $current): ThemePreference
    {
        $preference = $current;

        if ($request->query->has('theme')) {
            $preference = $this->preferenceFromThemeId($request->query->getString('theme'));
        }

        if ($request->query->has('scheme')) {
            $scheme = ThemeColorScheme::tryFromString($request->query->getString('scheme'));
            if ($scheme === null) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid scheme "%s"; expected auto, light, or dark.',
                    $request->query->getString('scheme'),
                ));
            }

            $preference = $preference->withScheme($scheme);
        }

        return $preference;
    }

    public function assertKnownThemeId(string $themeId): void
    {
        if (!in_array($themeId, $this->themeRegistry->ids(), true)) {
            throw new InvalidArgumentException(sprintf('Unknown theme id "%s".', $themeId));
        }
    }
}
