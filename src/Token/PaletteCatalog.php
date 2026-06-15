<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use Symfinity\UiKernel\Internal\TypeGuard;
use Symfony\Component\Yaml\Yaml;

final class PaletteCatalog
{
    /** @var array{contract: array<string, mixed>, generator: array<string, mixed>}|null */
    private static ?array $referenceConfig = null;

    private static ?int $referenceConfigMtime = null;

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
     * @return array<string, float> tinted mono hue degrees from bundle
     */
    public static function monoHues(): array
    {
        $hues = self::generator()['mono_hues'] ?? null;
        if (!is_array($hues)) {
            throw new \RuntimeException('generator.palette.mono_hues must be defined.');
        }

        $normalized = [];
        foreach ($hues as $tone => $degrees) {
            if (!is_string($tone) || !is_numeric($degrees)) {
                throw new \RuntimeException('generator.palette.mono_hues must be tone => float degrees.');
            }
            $normalized[$tone] = (float) $degrees;
        }

        return $normalized;
    }

    /**
     * @return list<string> all mono ref tone ids including achromatic neutral
     */
    public static function monoGrammarTones(): array
    {
        return [...self::monoTones(), 'neutral'];
    }

    public static function darkTailLEnd(): float
    {
        $end = self::generator()['dark_tail_l_end'] ?? null;
        if (is_numeric($end)) {
            return (float) $end;
        }

        return 0.09;
    }

    /**
     * @return array{0: float, 1: float} [min L, max L]
     */
    public static function lBounds(): array
    {
        $bounds = self::generator()['l_bounds'] ?? null;
        if (is_array($bounds) && array_is_list($bounds) && count($bounds) === 2
            && is_numeric($bounds[0]) && is_numeric($bounds[1])) {
            return [TypeGuard::numericFloat($bounds[0]), TypeGuard::numericFloat($bounds[1])];
        }

        return [0.0025, 0.92];
    }

    /**
     * @return array{0: float, 1: float}
     */
    public static function pureLBounds(): array
    {
        $bounds = self::generator()['pure_l_bounds'] ?? null;
        if (is_array($bounds) && array_is_list($bounds) && count($bounds) === 2
            && is_numeric($bounds[0]) && is_numeric($bounds[1])) {
            return [TypeGuard::numericFloat($bounds[0]), TypeGuard::numericFloat($bounds[1])];
        }

        return [1.0, 0.0];
    }

    public static function chromaPercent(): float
    {
        $percent = self::generator()['chroma_percent'] ?? null;
        if (is_numeric($percent)) {
            return (float) $percent;
        }

        return 100.0;
    }

    public static function interpolation(): string
    {
        return 'oklch';
    }

    public static function revision(): int
    {
        $revision = self::generator()['revision'] ?? 1;

        if (is_int($revision)) {
            return $revision;
        }

        if (is_numeric($revision)) {
            return (int) $revision;
        }

        return 1;
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
        self::$referenceConfigMtime = null;
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
        $configPath = dirname(__DIR__, 2) . '/config/packages/symfinity_ui_kernel.yaml';
        $mtime = filemtime($configPath);
        if ($mtime === false) {
            throw new \RuntimeException(sprintf('Bundle config "%s" is not readable.', $configPath));
        }

        if (self::$referenceConfig !== null && self::$referenceConfigMtime === $mtime) {
            return self::$referenceConfig;
        }

        self::$referenceConfig = null;
        self::$referenceConfigMtime = $mtime;
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

        $contractNode = $kernel['contract'] ?? null;
        $generatorNode = $kernel['generator'] ?? null;
        if (!is_array($contractNode) || !is_array($generatorNode)) {
            throw new \RuntimeException(sprintf(
                'Bundle config "%s" must define symfinity_ui_kernel.contract and generator.',
                $configPath,
            ));
        }

        $contract = $contractNode['palette'] ?? null;
        $generator = $generatorNode['palette'] ?? null;
        if (!is_array($contract) || !is_array($generator)) {
            throw new \RuntimeException(sprintf(
                'Bundle config "%s" must define symfinity_ui_kernel.contract.palette and generator.palette.',
                $configPath,
            ));
        }

        GeneratorPaletteConfigValidator::validate(
            TypeGuard::stringKeyMap($contract),
            TypeGuard::stringKeyMap($generator),
        );

        self::$referenceConfig = [
            'contract' => TypeGuard::stringKeyMap($contract),
            'generator' => TypeGuard::stringKeyMap($generator),
        ];

        return self::$referenceConfig;
    }
}
