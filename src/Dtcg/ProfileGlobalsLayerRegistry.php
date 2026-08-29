<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Dtcg;

use Symfinity\UiKernel\Contract\Layer\LayerRole;
use Symfinity\UiKernel\Contract\Layer\LayerStack;
use Symfinity\UiKernel\Contract\Layer\TokenLayer;
use Symfinity\UiKernel\Contract\Resolver\ResolvedGraphInterface;

/**
 * Loads platform profile globals (z-index + keyframes) as a base DTCG layer (078).
 */
final class ProfileGlobalsLayerRegistry
{
    public const LAYER_ID = 'platform-profile-globals';

    public function __construct(
        private readonly string $filePath,
        private readonly DtcgYamlReader $reader = new DtcgYamlReader(),
        private readonly LayeredTokenResolver $resolver = new LayeredTokenResolver(),
    ) {
    }

    public function layer(): TokenLayer
    {
        return $this->reader->read($this->filePath)->asLayer(self::LAYER_ID, LayerRole::Base);
    }

    public function resolvedGraph(): ResolvedGraphInterface
    {
        return $this->resolver->resolve(new LayerStack($this->layer()));
    }

    public function revision(): string
    {
        $contents = file_get_contents($this->filePath);
        if ($contents === false) {
            return 'missing';
        }

        return hash('xxh128', $contents);
    }

    public static function defaultFilePath(): string
    {
        return dirname(__DIR__, 2) . '/config/tokens/profile-globals.dtcg.yaml';
    }

    public static function fromDefaultPath(): self
    {
        return new self(self::defaultFilePath());
    }
}
