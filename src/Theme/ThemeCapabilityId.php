<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

enum ThemeCapabilityId: string
{
    case RuntimeSwitch = 'runtime_switch';
    case UserTokenOverlay = 'user_token_overlay';
    case SessionPreviewOverlay = 'session_preview_overlay';
    case AppDtcgOverride = 'app_dtcg_override';
    case NestedTheme = 'nested_theme';
    case InheritanceChain = 'inheritance_chain';
}
