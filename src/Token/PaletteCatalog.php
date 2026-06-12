<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use Symfony\Component\Yaml\Yaml;

final class PaletteCatalog
{
    /** @var array<string, mixed>|null */
    private static ?array $referenceConfig = null;

    /**
     * @return list<int>
     */
    public static function levels(): array
    {
        /** @var list<int> $levels */
        $levels = self::contract()['levels'];

        return $levels;
    }

    /**
     * @return list<int>
     */
    public static function alphaPercent(): array
    {
        /** @var list<int> $alpha */
        $alpha = self::contract()['alpha'];

        return $alpha;
    }

    /**
     * @return list<string>
     */
    public static function hueFamilies(): array
    {
        return self::hues();
    }

    /**
     * @return list<string>
     */
    public static function hues(): array
    {
        /** @var list<string> $hues */
        $hues = self::contract()['hues'];

        return $hues;
    }

    /**
     * @return list<string>
     */
    public static function forbiddenHues(): array
    {
        /** @var list<string> $hues */
        $hues = self::contract()['forbidden_hues'];

        return $hues;
    }

    /**
     * @return list<string>
     */
    public static function monoTones(): array
    {
        /** @var list<string> $tones */
        $tones = self::contract()['mono_tones'];

        return $tones;
    }

    /**
     * @return array<int, float>
     */
    public static function oklchLightnessCurve(string $key): array
    {
        return self::oklchLightnessCurveInternal($key);
    }

    public static function interpolation(): string
    {
        $interpolation = self::generator()['interpolation'] ?? 'oklch';

        return is_string($interpolation) ? $interpolation : 'oklch';
    }

    public static function revision(): int
    {
        $revision = self::generator()['revision'] ?? 1;

        return (int) $revision;
    }

    public static function hueChroma(string $hue): float
    {
        $map = self::generator()['hue_chroma'] ?? null;
        if (!is_array($map) || !isset($map[$hue])) {
            throw new \InvalidArgumentException(sprintf('Unknown hue chroma for "%s".', $hue));
        }

        return (float) $map[$hue];
    }

    /**
     * @return array<int, float>
     */
    private static function oklchLightnessCurveInternal(string $key): array
    {
        $lightness = self::generator()['lightness_curve'] ?? null;
        if (!is_array($lightness)) {
            throw new \RuntimeException('generator.palette.lightness_curve must be a mapping.');
        }

        $raw = $lightness[$key] ?? null;
        if (!is_array($raw) || $raw === []) {
            throw new \RuntimeException(sprintf('generator.palette.lightness_curve.%s must be a non-empty list.', $key));
        }

        if (!array_is_list($raw)) {
            /** @var array<int, float> $raw */
            return $raw;
        }

        $levels = self::levels();
        if (count($raw) !== count($levels)) {
            throw new \RuntimeException(sprintf(
                'generator.palette.lightness_curve.%s length (%d) must match contract.palette.levels (%d).',
                $key,
                count($raw),
                count($levels),
            ));
        }

        $curve = [];
        foreach ($levels as $index => $level) {
            $curve[$level] = (float) $raw[$index];
        }

        return $curve;
    }

    /**
     * @return array<string, string>
     */
    public static function lineages(): array
    {
        return BuiltinThemeCatalog::lineageDonors();
    }

    /**
     * @return list<array{id: string, label: string, layout: string, tone: string, colors: array<string, string>, hue_base: array<string, float>, mono_tones: array<string, array{hue: float, saturation: float}>, lineage?: string, scroll_motion?: bool, backdrop_blur?: string}>
     */
    public static function themes(): array
    {
        return BuiltinThemeCatalog::themes();
    }

    public static function reset(): void
    {
        self::$referenceConfig = null;
        BuiltinThemeCatalog::reset();
    }

    /**
     * @return array<string, mixed>
     */
    private static function contract(): array
    {
        $config = self::referenceConfig();

        /** @var array<string, mixed> $contract */
        $contract = $config['contract'];

        return $contract;
    }

    /**
     * @return array<string, mixed>
     */
    private static function generator(): array
    {
        $config = self::referenceConfig();

        /** @var array<string, mixed> $generator */
        $generator = $config['generator'];

        return $generator;
    }

    /**
     * @return array{contract: array<string, mixed>, generator: array<string, mixed>}
     */
    private static function referenceConfig(): array
    {
        if (self::$referenceConfig !== null) {
            return self::$referenceConfig;
        }

        $configPath = dirname(__DIR__, 2) . '/config/packages/symfinity_ui_kernel.yaml';
        /** @var array<string, mixed> $parsed */
        $parsed = Yaml::parseFile($configPath);

        /** @var array<string, mixed> $kernel */
        $kernel = $parsed['symfinity_ui_kernel'] ?? $parsed;

        if (($kernel['schema_version'] ?? null) !== ThemeTokenSchema::V1_0) {
            throw new \RuntimeException(sprintf(
                'Bundle config "%s" must set schema_version: "%s".',
                $configPath,
                ThemeTokenSchema::V1_0,
            ));
        }

        $contract = $kernel['contract']['palette'] ?? null;
        $generator = $kernel['generator']['palette'] ?? null;
        if (!is_array($contract) || !is_array($generator)) {
            throw new \RuntimeException(sprintf(
                'Bundle config "%s" must define symfinity_ui_kernel.contract.palette and generator.palette.',
                $configPath,
            ));
        }

        GeneratorPaletteConfigValidator::validate($contract, $generator);

        self::$referenceConfig = [
            'contract' => $contract,
            'generator' => $generator,
        ];

        return self::$referenceConfig;
    }
}
