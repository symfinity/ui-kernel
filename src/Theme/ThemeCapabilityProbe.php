<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

use Symfinity\UiKernel\Preview\SessionThemeInjectionPort;
use Symfinity\UiKernel\Token\UserTokenSet;

final class ThemeCapabilityProbe
{
    public function __construct(
        private readonly UserTokenSet $userTokens,
        private readonly ThemeRegistry $themeRegistry,
        private readonly ?SessionThemeInjectionPort $sessionThemeInjectionPort = null,
    ) {
    }

    /**
     * @return list<ThemeCapabilityId>
     */
    public function enabled(): array
    {
        $enabled = [];
        foreach (ThemeCapabilityId::cases() as $id) {
            if ($this->isEnabled($id)) {
                $enabled[] = $id;
            }
        }

        return $enabled;
    }

    public function isEnabled(ThemeCapabilityId $id): bool
    {
        return match ($id) {
            ThemeCapabilityId::RuntimeSwitch => true,
            ThemeCapabilityId::UserTokenOverlay => !$this->userTokens->isEmpty(),
            ThemeCapabilityId::SessionPreviewOverlay => $this->sessionThemeInjectionPort !== null,
            ThemeCapabilityId::AppDtcgOverride => $this->themeRegistry->hasAppLineages(),
        };
    }

    /**
     * @return list<string>
     */
    public function enabledIds(): array
    {
        return array_map(
            static fn (ThemeCapabilityId $id): string => $id->value,
            $this->enabled(),
        );
    }
}
