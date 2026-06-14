<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Dtcg;

use Symfinity\UiKernel\Contract\Token\AliasReference;
use Symfinity\UiKernel\Contract\Token\Token;
use Symfinity\UiKernel\Token\MonoTone;

/**
 * Rewrites tinted mono alias targets in a theme layer to match variant tone (077).
 *
 * DTCG files author with a neutral default (often cool); runtime tone comes from theme.meta.yaml.
 */
final class MonoToneThemeLayerRewriter
{
    /**
     * @param array<string, Token> $tokens
     *
     * @return array<string, Token>
     */
    public function rewrite(array $tokens, MonoTone $tone): array
    {
        $rewritten = [];

        foreach ($tokens as $path => $token) {
            if (!$token->isAlias()) {
                $rewritten[$path] = $token;

                continue;
            }

            /** @var AliasReference $alias */
            $alias = $token->value();
            $target = (string) $alias->target();
            $adjusted = self::rewriteMonoPath($target, $tone);

            if ($adjusted === $target) {
                $rewritten[$path] = $token;

                continue;
            }

            $rewritten[$path] = new Token(
                $token->path(),
                $token->type(),
                AliasReference::parse('{' . $adjusted . '}'),
                $token->description(),
                $token->extensions(),
            );
        }

        return $rewritten;
    }

    private static function rewriteMonoPath(string $path, MonoTone $tone): string
    {
        if (preg_match('/^color\.mono\.([a-z]+)\.(\d+)$/', $path, $matches) !== 1) {
            return $path;
        }

        if ($matches[1] === MonoTone::Pure->value) {
            return $path;
        }

        return sprintf('color.mono.%s.%s', $tone->value, $matches[2]);
    }
}
