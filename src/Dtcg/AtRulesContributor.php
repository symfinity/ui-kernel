<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Dtcg;

use Symfinity\UiKernel\Contract\Emitter\AtRulesContributorInterface;
use Symfinity\UiKernel\Contract\Exception\UnresolvableAliasException;
use Symfinity\UiKernel\Contract\Resolver\ResolvedGraphInterface;
use Symfinity\UiKernel\Contract\Token\AliasReference;
use Symfinity\UiKernel\Contract\Token\TokenInterface;
use Symfinity\UiKernel\Contract\Token\TokenPath;

/**
 * Renders z-index custom properties and global {@code @keyframes} from DTCG tokens (078).
 */
final class AtRulesContributor implements AtRulesContributorInterface
{
    public function emitZIndexVars(ResolvedGraphInterface $graph): string
    {
        $lines = [':root {'];
        foreach ($graph->all() as $path => $token) {
            if (!str_starts_with($path, 'zIndex.')) {
                continue;
            }

            $symfinity = $this->symfinityExtensions($token);
            $cssVar = $symfinity['cssVar'] ?? null;
            if (!\is_string($cssVar) || $cssVar === '') {
                continue;
            }

            $value = $token->value();
            if (!\is_int($value) && !\is_float($value) && !\is_string($value)) {
                continue;
            }

            $lines[] = sprintf('  %s: %s;', $cssVar, $value);
        }
        $lines[] = '}';

        return implode("\n", $lines);
    }

    public function contribute(ResolvedGraphInterface $graph): string
    {
        $blocks = [];
        $disabledOnReduce = [];

        foreach ($graph->all() as $path => $token) {
            if (!str_starts_with($path, 'keyframes.')) {
                continue;
            }

            $symfinity = $this->symfinityExtensions($token);
            if (($symfinity['atRule'] ?? null) !== 'keyframes') {
                continue;
            }

            $name = \is_string($token->value()) ? $token->value() : substr($path, \strlen('keyframes.'));
            $steps = $symfinity['steps'] ?? [];
            if (!\is_array($steps) || $steps === []) {
                continue;
            }

            $lines = [sprintf('@keyframes %s {', $name)];
            foreach ($steps as $step) {
                if (!\is_array($step)) {
                    continue;
                }
                $offset = $step['offset'] ?? null;
                $properties = $step['properties'] ?? null;
                if (!\is_string($offset) || !\is_array($properties)) {
                    continue;
                }

                $propParts = [];
                foreach ($properties as $property => $rawValue) {
                    if (!\is_string($property) || !\is_string($rawValue)) {
                        continue;
                    }
                    $propParts[] = sprintf(
                        '%s: %s',
                        $property,
                        $this->resolveStepValue($rawValue, $graph),
                    );
                }
                $lines[] = sprintf('  %s { %s; }', $offset, implode('; ', $propParts));
            }
            $lines[] = '}';
            $blocks[] = implode("\n", $lines);

            if (($symfinity['reducedMotion'] ?? null) === 'disable') {
                $disabledOnReduce[] = $name;
            }
        }

        if ($disabledOnReduce !== []) {
            $reduceLines = ['@media (prefers-reduced-motion: reduce) {'];
            foreach ($disabledOnReduce as $name) {
                $reduceLines[] = sprintf('  @keyframes %s {', $name);
                $reduceLines[] = '    from, to { opacity: 1; }';
                $reduceLines[] = '  }';
            }
            $reduceLines[] = '}';
            $blocks[] = implode("\n", $reduceLines);
        }

        return implode("\n", $blocks);
    }

    /**
     * @return array<string, mixed>
     */
    private function symfinityExtensions(TokenInterface $token): array
    {
        $extensions = $token->extensions();
        $symfinity = $extensions['symfinity'] ?? [];

        return \is_array($symfinity) ? $symfinity : [];
    }

    private function resolveStepValue(string $rawValue, ResolvedGraphInterface $graph): string
    {
        if (!AliasReference::isAlias($rawValue)) {
            return $rawValue;
        }

        $alias = AliasReference::parse($rawValue);
        if (!$graph->has($alias->target())) {
            throw new UnresolvableAliasException(TokenPath::fromString('keyframes.step'), $alias->target());
        }

        $resolved = $graph->get($alias->target())->value();

        return \is_scalar($resolved) ? (string) $resolved : $rawValue;
    }
}
