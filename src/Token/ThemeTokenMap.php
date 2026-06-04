<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use Symfinity\UiKernel\Theme\LayoutProfile;

/**
 * Short theme YAML keys (e.g. space-md) ↔ CSS variables (--ui-space-md).
 */
final class ThemeTokenMap
{
    public const CSS_PREFIX = '--ui-';

    /**
     * @return list<string>
     */
    public static function requiredShortKeys(string $schemaVersion = ThemeTokenSchema::V1_0): array
    {
        ThemeTokenSchema::requiredKeys($schemaVersion);

        return array_map(
            static fn (string $cssVar): string => self::cssVarToShortKey($cssVar),
            ThemeTokenSchema::LAYOUT_KEYS,
        );
    }

    public static function shortKeyToCssVar(string $shortKey): string
    {
        if (str_starts_with($shortKey, self::CSS_PREFIX)) {
            throw new \InvalidArgumentException(sprintf(
                'Theme token key "%s" must not include "%s"; use short names only (e.g. space-md).',
                $shortKey,
                self::CSS_PREFIX,
            ));
        }

        return self::CSS_PREFIX . $shortKey;
    }

    public static function cssVarToShortKey(string $cssVar): string
    {
        if (!str_starts_with($cssVar, self::CSS_PREFIX)) {
            throw new \InvalidArgumentException(sprintf('Expected CSS variable "%s" to start with "%s".', $cssVar, self::CSS_PREFIX));
        }

        return substr($cssVar, strlen(self::CSS_PREFIX));
    }

    /**
     * @param array<string, string> $shortTokens
     *
     * @return array<string, string>
     */
    public static function toCssVariables(array $shortTokens): array
    {
        $css = [];
        foreach ($shortTokens as $shortKey => $value) {
            if (!is_string($shortKey) || !is_string($value) || $value === '') {
                throw new \InvalidArgumentException('Theme tokens must be a map of non-empty strings.');
            }

            $css[self::shortKeyToCssVar($shortKey)] = $value;
        }

        return $css;
    }

    /**
     * @param array<string, string> $shortTokens
     */
    public static function assertComplete(array $shortTokens, string $schemaVersion, string $context): void
    {
        foreach (self::requiredShortKeys($schemaVersion) as $required) {
            if (!isset($shortTokens[$required]) || $shortTokens[$required] === '') {
                throw new \InvalidArgumentException(sprintf(
                    '%s: missing required token "%s" (schema %s).',
                    $context,
                    $required,
                    $schemaVersion,
                ));
            }
        }
    }

    /**
     * Default appearance tokens for a layout lineage (PresetRegistry fallback).
     *
     * @return array<string, string>
     */
    public static function presetShortTokensFor(LayoutProfile $layout): array
    {
        $registry = new PresetRegistry();
        $css = $registry->tokensFor($layout, ThemeTokenSchema::V1_0);
        $short = [];
        foreach ($css as $cssVar => $value) {
            $short[self::cssVarToShortKey($cssVar)] = $value;
        }

        return $short;
    }
}
