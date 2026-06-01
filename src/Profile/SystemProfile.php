<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Profile;

final readonly class SystemProfile
{
    public const DEFAULT_ID = 'chameleon-default';

    /** @var array<string, int> */
    private const DEFAULT_BREAKPOINTS = [
        'sm' => 640,
        'md' => 768,
        'lg' => 1024,
        'xl' => 1280,
        '2xl' => 1536,
    ];

    /** @var array<string, int> */
    private const DEFAULT_CONTAINER_MAX_WIDTHS = [
        'md' => 720,
        'lg' => 960,
        'xl' => 1140,
        '2xl' => 1320,
    ];

    /** @var array<string, int> */
    private const Z_INDEX_LAYERS = [
        'dropdown' => 1000,
        'sticky' => 1020,
        'fixed' => 1030,
        'modal-backdrop' => 1040,
        'modal' => 1050,
        'popover' => 1060,
        'tooltip' => 1070,
        'toast' => 1080,
    ];

    /**
     * @param array<string, int>    $breakpoints
     * @param array<string, int>    $containerMaxWidths
     */
    public function __construct(
        public string $id,
        public int $columns,
        public array $breakpoints,
        public array $containerMaxWidths,
    ) {
    }

    public static function chameleonDefault(): self
    {
        return new self(
            self::DEFAULT_ID,
            12,
            self::DEFAULT_BREAKPOINTS,
            self::DEFAULT_CONTAINER_MAX_WIDTHS,
        );
    }

    /**
     * @param array{
     *     id?: string,
     *     columns?: int,
     *     breakpoints?: array<string, int>,
     *     container_max_widths?: array<string, int>
     * } $config
     */
    public static function fromConfig(array $config): self
    {
        $base = self::chameleonDefault();
        $id = $config['id'] ?? $base->id;
        $columns = $config['columns'] ?? $base->columns;

        $breakpoints = $base->breakpoints;
        foreach ($config['breakpoints'] ?? [] as $name => $px) {
            $breakpoints[(string) $name] = (int) $px;
        }

        $containerMaxWidths = $base->containerMaxWidths;
        foreach ($config['container_max_widths'] ?? [] as $name => $px) {
            $containerMaxWidths[(string) $name] = (int) $px;
        }

        return new self($id, (int) $columns, $breakpoints, $containerMaxWidths);
    }

    public function breakpointPx(string $name): ?int
    {
        return $this->breakpoints[$name] ?? null;
    }

    /**
     * @return array<string, int>
     */
    public function zIndexLayers(): array
    {
        return self::Z_INDEX_LAYERS;
    }

    public function hash(): string
    {
        $payload = json_encode([
            $this->id,
            $this->columns,
            $this->breakpoints,
            $this->containerMaxWidths,
        ], \JSON_THROW_ON_ERROR);

        return hash('xxh128', $payload);
    }
}
