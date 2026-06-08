<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Maps interactive button variants to canonical semantic colour tokens (018, 060).
 */
final class ButtonVariantMap
{
    /** @var list<string> */
    public const SEMANTIC_VARIANTS = SemanticVariant::ALL;

    public static function semanticTokenKey(string $variant): string
    {
        return SemanticVariant::tokenKey($variant);
    }

    /**
     * @return array<string, list<string>> canonical semantic variant => data-ui-variant attribute values
     */
    public static function cssVariantSelectors(): array
    {
        $selectors = [];
        foreach (SemanticVariant::ALL as $variant) {
            $selectors[$variant] = [$variant];
        }

        return $selectors;
    }
}
