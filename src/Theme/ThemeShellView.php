<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

/**
 * View-model for host layout chrome: SchemeSwitch (ux-blocks-core) and data-theme hooks.
 *
 * @phpstan-type SchemeSwitcherLink array{scheme: string, url: string, active: bool}
 * @phpstan-type ThemeSwitcherLink array{id: string, url: string, active: bool}
 */
final readonly class ThemeShellView
{
    public const SCHEME_ENDPOINT = '/_ui/theme/scheme';

    /**
     * @param list<SchemeSwitcherLink> $schemeSwitcherLinks
     */
    public function __construct(
        public ?Theme $activeTheme,
        public string $scheme,
        public string $colorScheme,
        public array $schemeSwitcherLinks,
        public string $schemeEndpoint = self::SCHEME_ENDPOINT,
    ) {
    }

    public static function empty(string $defaultColorScheme = 'dark'): self
    {
        return new self(
            activeTheme: null,
            scheme: ThemeColorScheme::Auto->value,
            colorScheme: $defaultColorScheme,
            schemeSwitcherLinks: [],
        );
    }

    /**
     * @return array{
     *     activeTheme: Theme|null,
     *     scheme: string,
     *     colorScheme: string,
     *     schemeSwitcherLinks: list<SchemeSwitcherLink>,
     *     schemeEndpoint: string
     * }
     */
    public function toArray(): array
    {
        return [
            'activeTheme' => $this->activeTheme,
            'scheme' => $this->scheme,
            'colorScheme' => $this->colorScheme,
            'schemeSwitcherLinks' => $this->schemeSwitcherLinks,
            'schemeEndpoint' => $this->schemeEndpoint,
        ];
    }
}
