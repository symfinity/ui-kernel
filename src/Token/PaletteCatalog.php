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
     * @return list<int>
     */
    public static function rampLevels(): array
    {
        $generator = self::generator();
        /** @var list<int> $levels */
        $levels = $generator['ramp_levels'] ?? self::levels();

        return $levels;
    }

    /**
     * @return array<int, float>
     */
    public static function levelLightness(): array
    {
        return self::levelLightnessCurve('default');
    }

    /**
     * @return array<int, float>
     */
    public static function levelLightnessPure(): array
    {
        return self::levelLightnessCurve('pure');
    }

    /**
     * @return array<int, float>
     */
    private static function levelLightnessCurve(string $key): array
    {
        $lightness = self::generator()['lightness'] ?? null;
        if (!is_array($lightness)) {
            throw new \RuntimeException('generator.palette.lightness must be a mapping.');
        }

        $raw = $lightness[$key] ?? null;
        if (!is_array($raw) || $raw === []) {
            throw new \RuntimeException(sprintf('generator.palette.lightness.%s must be a non-empty list.', $key));
        }

        if (!array_is_list($raw)) {
            /** @var array<int, float> $raw */
            return $raw;
        }

        $rampLevels = self::rampLevels();
        if (count($raw) !== count($rampLevels)) {
            throw new \RuntimeException(sprintf(
                'generator.palette.lightness.%s length (%d) must match ramp_levels (%d).',
                $key,
                count($raw),
                count($rampLevels),
            ));
        }

        $curve = [];
        foreach ($rampLevels as $index => $level) {
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

        self::$referenceConfig = [
            'contract' => $contract,
            'generator' => $generator,
        ];

        return self::$referenceConfig;
    }
}
