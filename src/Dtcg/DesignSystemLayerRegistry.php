<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Dtcg;

use Symfinity\UiKernel\Contract\Layer\LayerRole;
use Symfinity\UiKernel\Contract\Layer\TokenLayer;
use Symfinity\UiKernel\Dtcg\Exception\UnknownDesignSystemException;

/**
 * File-backed registry: {@code config/design-systems/{id}.dtcg.yaml} → design_system layer (077).
 */
final class DesignSystemLayerRegistry
{
    public const DEFAULT_ID = 'symfinity';

    public function __construct(
        private readonly string $directory,
        private readonly DtcgYamlReader $reader = new DtcgYamlReader(),
    ) {
    }

    public function defaultId(): string
    {
        return self::DEFAULT_ID;
    }

    public function has(string $id): bool
    {
        return is_file($this->pathFor($id));
    }

    public function get(string $id): TokenLayer
    {
        $path = $this->pathFor($id);
        if (!is_file($path)) {
            throw new UnknownDesignSystemException($id, $this->directory);
        }

        return $this->reader->read($path)->asLayer('design_system:' . $id, LayerRole::DesignSystem);
    }

    private function pathFor(string $id): string
    {
        return $this->directory . '/' . $id . '.dtcg.yaml';
    }

    public static function defaultDirectory(): string
    {
        return dirname(__DIR__, 2) . '/config/design-systems';
    }

    public static function fromDefaultDirectory(): self
    {
        return new self(self::defaultDirectory());
    }
}
