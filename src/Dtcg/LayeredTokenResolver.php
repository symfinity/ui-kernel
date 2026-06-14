<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Dtcg;

use Symfinity\UiKernel\Contract\Exception\ReferenceCycleException;
use Symfinity\UiKernel\Contract\Exception\TokenTypeMismatchException;
use Symfinity\UiKernel\Contract\Exception\UnresolvableAliasException;
use Symfinity\UiKernel\Contract\Layer\LayerStack;
use Symfinity\UiKernel\Contract\Resolver\ResolvedGraph;
use Symfinity\UiKernel\Contract\Resolver\ResolvedGraphInterface;
use Symfinity\UiKernel\Contract\Resolver\TokenResolverInterface;
use Symfinity\UiKernel\Contract\Token\AliasReference;
use Symfinity\UiKernel\Contract\Token\Token;
use Symfinity\UiKernel\Contract\Token\TokenType;

/**
 * Merges a layer stack and resolves all `{alias}` references to concrete values (076).
 *
 * Precedence: base < design_system < theme. Recursive alias resolution with a visited-set
 * cycle guard; located errors on unresolvable target, cycle, and type mismatch.
 */
final class LayeredTokenResolver implements TokenResolverInterface
{
    public function resolve(LayerStack $stack): ResolvedGraphInterface
    {
        $merged = $stack->merge();

        $resolved = [];
        foreach ($merged as $path => $token) {
            $resolved[$path] = $this->resolveToken($token, $merged, []);
        }

        return new ResolvedGraph($resolved, $stack->signature());
    }

    /**
     * @param array<string, Token> $merged
     * @param list<string>         $visited
     */
    private function resolveToken(Token $token, array $merged, array $visited): Token
    {
        $pathKey = (string) $token->path();

        if (\in_array($pathKey, $visited, true)) {
            throw new ReferenceCycleException([...$visited, $pathKey]);
        }

        if (!$token->isAlias()) {
            return $token;
        }

        /** @var AliasReference $alias */
        $alias = $token->value();
        $targetKey = (string) $alias->target();

        if (!isset($merged[$targetKey])) {
            throw new UnresolvableAliasException($token->path(), $alias->target());
        }

        $resolvedTarget = $this->resolveToken($merged[$targetKey], $merged, [...$visited, $pathKey]);

        if (
            $token->type() !== TokenType::Unknown
            && $resolvedTarget->type() !== TokenType::Unknown
            && $token->type() !== $resolvedTarget->type()
        ) {
            throw new TokenTypeMismatchException($token->path(), $token->type(), $resolvedTarget->type());
        }

        $effectiveType = $token->type() !== TokenType::Unknown ? $token->type() : $resolvedTarget->type();

        return $token->withResolvedValue($resolvedTarget->value(), $effectiveType);
    }
}
