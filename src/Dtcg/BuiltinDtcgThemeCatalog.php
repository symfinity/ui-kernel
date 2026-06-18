<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Dtcg;

use Psr\Log\LoggerInterface;
use Symfinity\UiKernel\Dtcg\Exception\InvalidThemeSchemaException;
use Symfinity\UiKernel\Internal\TypeGuard;
use Symfinity\UiKernel\Theme\LayoutProfile;
use Symfinity\UiKernel\Token\GeneratorPaletteConfigValidator;
use Symfinity\UiKernel\Token\MonoTone;
use Symfinity\UiKernel\Token\ThemePaletteMetaNormalizer;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads built-in themes from {@code config/themes/{lineage}/} DTCG layout (077).
 *
 * Merges consumer app {@code config/themes/} with bundle defaults — app lineage overrides bundle (086).
 */
final class BuiltinDtcgThemeCatalog
{
    /** @var list<BuiltinThemeVariant>|null */
    private static ?array $variants = null;

    /** @var array<string, string>|null lineage => donor variant id */
    private static ?array $lineageDonors = null;

    private static ?string $cacheKey = null;

    public function __construct(
        private readonly string $bundleThemesDirectory,
        private readonly ?string $appThemesDirectory = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * @return list<BuiltinThemeVariant>
     */
    public function all(): array
    {
        $this->ensureLoaded();

        return self::$variants ?? throw new \LogicException('Built-in DTCG themes failed to load.');
    }

    /**
     * @return array<string, string>
     */
    public function lineageDonors(): array
    {
        $this->ensureLoaded();

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
        self::$cacheKey = null;
    }

    private function ensureLoaded(): void
    {
        $key = $this->cacheKey();
        if (self::$cacheKey === $key && self::$variants !== null) {
            return;
        }

        self::reset();
        self::$cacheKey = $key;
        $this->load();
    }

    private function cacheKey(): string
    {
        return ($this->appThemesDirectory ?? '') . '|' . $this->bundleThemesDirectory;
    }

    private function load(): void
    {
        if (!is_dir($this->bundleThemesDirectory)) {
            throw new \RuntimeException(sprintf('Built-in theme directory "%s" is missing.', $this->bundleThemesDirectory));
        }

        $lineageDirs = $this->mergedLineageDirectories();
        if ($lineageDirs === []) {
            throw new \RuntimeException(sprintf('No built-in DTCG themes found in "%s".', $this->bundleThemesDirectory));
        }

        $variants = [];
        $lineageDonors = [];
        $seenIds = [];

        foreach ($lineageDirs as $lineageKey => ['path' => $lineageDir, 'source' => $source]) {
            try {
                $loaded = $this->loadLineage($lineageDir, $lineageKey, $source);
            } catch (\Throwable $e) {
                if ($source === 'app') {
                    $this->logger?->warning('Skipping invalid app theme lineage: {message}', [
                        'message' => $e->getMessage(),
                        'path' => $lineageDir,
                    ]);

                    continue;
                }

                throw $e;
            }

            foreach ($loaded['variants'] as $variant) {
                $id = $variant->id();
                if (isset($seenIds[$id])) {
                    throw new \InvalidArgumentException(sprintf(
                        'Duplicate theme variant id "%s" across merged catalog.',
                        $id,
                    ));
                }
                $seenIds[$id] = true;
                $variants[] = $variant;
            }

            if ($loaded['donor'] !== null) {
                $lineageDonors[$lineageKey] = $loaded['donor'];
            }
        }

        if ($variants === []) {
            throw new \RuntimeException(sprintf('No built-in DTCG themes found in "%s".', $this->bundleThemesDirectory));
        }

        self::$variants = $variants;
        self::$lineageDonors = $lineageDonors;
    }

    /**
     * @return array<string, array{path: string, source: string}>
     */
    private function mergedLineageDirectories(): array
    {
        $merged = [];

        foreach ($this->discoverLineageDirectories($this->bundleThemesDirectory, 'kernel') as $key => $path) {
            $merged[$key] = ['path' => $path, 'source' => 'kernel'];
        }

        if ($this->appThemesDirectory !== null && is_dir($this->appThemesDirectory)) {
            foreach ($this->discoverLineageDirectories($this->appThemesDirectory, 'app') as $key => $path) {
                $merged[$key] = ['path' => $path, 'source' => 'app'];
            }
        }

        ksort($merged);

        return $merged;
    }

    /**
     * @return array<string, string> lineage key => absolute directory path
     */
    private function discoverLineageDirectories(string $root, string $source): array
    {
        if (!is_dir($root)) {
            return [];
        }

        $lineages = glob($root . '/*', \GLOB_ONLYDIR) ?: [];
        $result = [];

        foreach ($lineages as $lineageDir) {
            $lineageKey = basename($lineageDir);
            if ($lineageKey === 'README' || str_starts_with($lineageKey, '_')) {
                continue;
            }

            $metaPath = $lineageDir . '/theme.meta.yaml';
            if (!is_file($metaPath)) {
                if ($source === 'app') {
                    $this->logger?->warning('Skipping app theme lineage without theme.meta.yaml at {path}', [
                        'path' => $lineageDir,
                    ]);
                }

                continue;
            }

            $result[$lineageKey] = $lineageDir;
        }

        return $result;
    }

    /**
     * @return array{variants: list<BuiltinThemeVariant>, donor: ?string}
     */
    private function loadLineage(string $lineageDir, string $lineageKey, string $catalogSource): array
    {
        $metaPath = $lineageDir . '/theme.meta.yaml';
        if (!is_file($metaPath)) {
            throw new \InvalidArgumentException(sprintf('Theme meta "%s" is missing.', $metaPath));
        }

        /** @var mixed $parsed */
        $parsed = Yaml::parseFile($metaPath);
        if (!is_array($parsed)) {
            throw new \InvalidArgumentException(sprintf('Theme meta "%s" must be a YAML mapping.', $metaPath));
        }

        $legacyKernel = $parsed['symfinity_ui_kernel'] ?? null;
        if (is_array($legacyKernel) && isset($legacyKernel['themes'])) {
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

        $variantList = $meta['variants'] ?? null;
        if (!is_array($variantList) || $variantList === []) {
            throw new \InvalidArgumentException(sprintf('Theme meta "%s" must define variants.', $metaPath));
        }

        $paletteNorm = ThemePaletteMetaNormalizer::normalize(TypeGuard::stringKeyMap($palette));
        /** @var list<array<string, mixed>> $variantEntries */
        $variantEntries = [];
        foreach ($variantList as $entry) {
            if (is_array($entry)) {
                $variantEntries[] = TypeGuard::stringKeyMap($entry);
            }
        }
        GeneratorPaletteConfigValidator::validateVariantTones($variantEntries);
        $designSystemId = is_string($meta['design_system_id'] ?? null) && $meta['design_system_id'] !== ''
            ? $meta['design_system_id']
            : DesignSystemLayerRegistry::DEFAULT_ID;

        $layout = self::layoutForLineage($lineage);
        $variants = [];
        $donor = null;

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

            $mode = is_string($entry['mode'] ?? null) && in_array($entry['mode'], ['light', 'dark'], true)
                ? $entry['mode']
                : (str_ends_with($id, '-dark') ? 'dark' : 'light');

            $variant = new BuiltinThemeVariant(
                $id,
                $label,
                $lineage,
                $designSystemId,
                $layout,
                MonoTone::from(TypeGuard::string($entry['tone'] ?? 'slate')),
                $layerPath,
                $paletteNorm,
                scrollMotion: (bool) ($entry['scroll_motion'] ?? false),
                backdropBlur: is_string($entry['backdrop_blur'] ?? null) ? $entry['backdrop_blur'] : '0',
                mode: $mode,
                catalogSource: $catalogSource,
            );

            if ($donor === null) {
                $donor = $id;
            }

            $variants[] = $variant;
        }

        return ['variants' => $variants, 'donor' => $donor];
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

    public static function fromConfiguredDirectories(?string $appThemesDirectory, ?string $bundleThemesDirectory = null): self
    {
        return new self(
            $bundleThemesDirectory ?? self::defaultDirectory(),
            $appThemesDirectory,
        );
    }
}
