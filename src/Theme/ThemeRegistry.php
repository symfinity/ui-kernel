<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

use InvalidArgumentException;
use Symfinity\UiKernel\Token\RegistryResolutionPolicy;
use Symfinity\UiKernel\Token\ThemeTokenResolver;
use Symfinity\UiKernel\Token\UserTokenSet;

final class ThemeRegistry
{
    /** @var array<string, Theme> */
    private array $themes = [];

    private ThemeTokenResolver $resolver;

    private UserTokenSet $userTokens;

    public function __construct(?ThemeTokenResolver $resolver = null, ?UserTokenSet $userTokens = null)
    {
        $this->resolver = $resolver ?? new ThemeTokenResolver();
        $this->userTokens = $userTokens ?? new UserTokenSet();

        foreach (ThemeCatalog::all($this->resolver, $this->userTokens) as $theme) {
            $this->register($theme);
        }
    }

    public function register(Theme $theme): void
    {
        $id = $theme->id();
        RegistryResolutionPolicy::assertUniqueThemeId($id, isset($this->themes[$id]));

        $this->themes[$id] = $theme;
    }

    public function get(string $id): Theme
    {
        if (!isset($this->themes[$id])) {
            throw new InvalidArgumentException(sprintf('Unknown theme "%s".', $id));
        }

        return $this->themes[$id];
    }

    public function resolve(?string $id): Theme
    {
        if ($id === null || $id === '') {
            return $this->get('default');
        }

        if (!isset($this->themes[$id])) {
            return $this->get('default');
        }

        return $this->themes[$id];
    }

    /**
     * @return list<string>
     */
    public function ids(): array
    {
        return array_keys($this->themes);
    }

    /**
     * @return list<Theme>
     */
    public function all(): array
    {
        return array_values($this->themes);
    }
}
