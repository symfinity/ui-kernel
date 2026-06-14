<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Dtcg;

use Symfinity\UiKernel\Dtcg\Exception\InvalidThemeSchemaException;
use Symfinity\UiKernel\Internal\TypeGuard;
use Symfinity\UiKernel\Theme\LayoutProfile;
use Symfinity\UiKernel\Token\MonoTone;
use Symfinity\UiKernel\Token\ThemePaletteMetaNormalizer;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads built-in themes from {@code config/themes/{lineage}/} DTCG layout (077).
 */
final class BuiltinDtcgThemeCatalog
{
    /** @var list<BuiltinThemeVariant>|null */
    private static ?array $variants = null;

    /** @var array<string, string>|null lineage => donor variant id */
    private static ?array $lineageDonors = null;

    public function __construct(
        private readonly string $themesDirectory,
    ) {
    }

    /**
     * @return list<BuiltinThemeVariant>
     */
    public function all(): array
    {
        if (self::$variants !== null) {
            return self::$variants;
        }

        self::load();

        return self::$variants ?? throw new \LogicException('Built-in DTCG themes failed to load.');
    }

    /**
     * @return array<string, string>
     */
    public function lineageDonors(): array
    {
        if (self::$lineageDonors !== null) {
            return self::$lineageDonors;
        }

        self::load();

        return self::$lineageDonors ?? throw new \LogicException('Built-in lineage donors failed to load.');
    }

    public function get(string $id): BuiltinThemeVariant
    {
        foreach ($this->all() as $variant) {
            if ($variant->id() === $id) {
                return $variant;
            }
        }

        throw new \InvalidArgumentException(sprintf('Unknown built-in theme id "%s".', $id));
    }

    public static function reset(): void
    {
        self::$variants = null;
        self::$lineageDonors = null;
    }

    private function load(): void
    {
        if (!is_dir($this->themesDirectory)) {
            throw new \RuntimeException(sprintf('Built-in theme directory "%s" is missing.', $this->themesDirectory));
        }

        $lineages = glob($this->themesDirectory . '/*', \GLOB_ONLYDIR) ?: [];
        sort($lineages);

        $variants = [];
        $lineageDonors = [];

        foreach ($lineages as $lineageDir) {
            $lineageKey = basename($lineageDir);
            if ($lineageKey === 'README' || str_starts_with($lineageKey, '_')) {
                continue;
            }

            $metaPath = $lineageDir . '/theme.meta.yaml';
            if (!is_file($metaPath)) {
                continue;
            }

            /** @var mixed $parsed */
            $parsed = Yaml::parseFile($metaPath);
            if (!is_array($parsed)) {
                throw new \InvalidArgumentException(sprintf('Theme meta "%s" must be a YAML mapping.', $metaPath));
            }

            if (isset($parsed['symfinity_ui_kernel']['themes'])) {
                throw InvalidThemeSchemaException::legacySchema($metaPath);
            }

            /** @var array<string, mixed> $meta */
            $meta = $parsed;
            $lineage = TypeGuard::string($meta['lineage'] ?? $lineageKey);
            if ($lineage !== $lineageKey) {
                throw new \InvalidArgumentException(sprintf(
                    'Theme meta "%s" lineage "%s" must match directory "%s".',
                    $metaPath,
                    $lineage,
                    $lineageKey,
                ));
            }

            $palette = $meta['palette'] ?? null;
            if (!is_array($palette)) {
                throw new \InvalidArgumentException(sprintf('Theme meta "%s" must define palette.', $metaPath));
            }

            $paletteNorm = ThemePaletteMetaNormalizer::normalize(TypeGuard::stringKeyMap($palette));
            $designSystemId = is_string($meta['design_system_id'] ?? null) && $meta['design_system_id'] !== ''
                ? $meta['design_system_id']
                : DesignSystemLayerRegistry::DEFAULT_ID;

            $variantList = $meta['variants'] ?? null;
            if (!is_array($variantList) || $variantList === []) {
                throw new \InvalidArgumentException(sprintf('Theme meta "%s" must define variants.', $metaPath));
            }

            $layout = self::layoutForLineage($lineage);

            foreach ($variantList as $entry) {
                if (!is_array($entry)) {
                    throw new \InvalidArgumentException(sprintf('Theme meta "%s" variants must be a list of mappings.', $metaPath));
                }

                $id = $entry['id'] ?? null;
                $layerFile = $entry['layer_file'] ?? null;
                if (!is_string($id) || $id === '' || !is_string($layerFile) || $layerFile === '') {
                    throw new \InvalidArgumentException(sprintf('Theme meta "%s" variant entry requires id and layer_file.', $metaPath));
                }

                $layerPath = $lineageDir . '/' . $layerFile;
                if (!is_file($layerPath)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Theme variant "%s" layer_file "%s" not found.',
                        $id,
                        $layerPath,
                    ));
                }

                $label = is_string($entry['label'] ?? null) && $entry['label'] !== ''
                    ? $entry['label']
                    : $id;

                $variant = new BuiltinThemeVariant(
                    $id,
                    $label,
                    $lineage,
                    $designSystemId,
                    $layout,
                    MonoTone::from(TypeGuard::string($entry['tone'] ?? 'cool')),
                    $layerPath,
                    $paletteNorm,
                    scrollMotion: (bool) ($entry['scroll_motion'] ?? false),
                    backdropBlur: is_string($entry['backdrop_blur'] ?? null) ? $entry['backdrop_blur'] : '0',
                );

                if (!isset($lineageDonors[$lineage])) {
                    $lineageDonors[$lineage] = $id;
                }

                $variants[] = $variant;
            }
        }

        if ($variants === []) {
            throw new \RuntimeException(sprintf('No built-in DTCG themes found in "%s".', $this->themesDirectory));
        }

        self::$variants = $variants;
        self::$lineageDonors = $lineageDonors;
    }

    private static function layoutForLineage(string $lineage): LayoutProfile
    {
        return match ($lineage) {
            'utility' => LayoutProfile::Utility,
            default => LayoutProfile::Semantic,
        };
    }

    public static function defaultDirectory(): string
    {
        return dirname(__DIR__, 2) . '/config/themes';
    }

    public static function fromDefaultDirectory(): self
    {
        return new self(self::defaultDirectory());
    }
}
