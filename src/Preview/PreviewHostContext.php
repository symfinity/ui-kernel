<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Preview;

final readonly class PreviewHostContext
{
    public function __construct(
        public string $token,
        public string $resolvedThemeId,
        public string $css,
        public bool $isPreviewActive,
        public ?string $activePolarity = null,
        public ?string $packPath = null,
        public bool $expired = false,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function banner(): array
    {
        return [
            'theme_id' => $this->resolvedThemeId,
            'polarity' => $this->activePolarity,
            'pack_path' => $this->packPath,
            'exit_url' => '/ui-themer',
            'expired' => $this->expired,
        ];
    }
}
