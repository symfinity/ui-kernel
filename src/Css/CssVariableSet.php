<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Css;

use InvalidArgumentException;
use Symfinity\UiKernel\Contract\Resolver\ResolvedGraphInterface;
use Symfinity\UiKernel\Contract\Token\TokenInterface;
use Symfinity\UiKernel\Contract\Token\TokenPath;
use Symfinity\UiKernel\Dtcg\CssEmissionFilter;
use Symfinity\UiKernel\Palette\OklchColorSpace;
use Symfinity\UiKernel\Palette\OklchTuple;

/**
 * Bridges a resolved DTCG graph to the kernel's `--ui-*` custom-property map (076).
 *
 * The token path ↔ CSS key mapping is a deterministic dot/dash bijection:
 * `color.primary` ⇄ `--ui-color-primary`, preserving order for output parity.
 */
final class CssVariableSet
{
    private const PREFIX = '--ui-';

    /**
     * Maps nested DTCG paths to legacy flat {@code --ui-*} keys (077 parity).
     *
     * @var array<string, string> dotted path => dotted path alias before CSS key conversion
     */
    private const EMISSION_PATH_ALIASES = [
        'color.surface.base' => 'color.surface',
        'color.text.default' => 'color.text',
        'color.border.default' => 'color.border',
    ];

    public function __construct(
        private readonly OklchColorSpace $colorSpace = new OklchColorSpace(),
    ) {
    }

    /**
     * @return array<string, string> `--ui-*` => CSS value (insertion order preserved)
     */
    public function fromResolvedGraph(ResolvedGraphInterface $graph): array
    {
        return $this->variablesFromGraph($graph, new CssEmissionFilter());
    }

    public function variablesFromGraph(ResolvedGraphInterface $graph, CssEmissionFilter $filter): array
    {
        return $this->fromEmitTokens($filter->emitTokens($graph));
    }

    /**
     * @param array<string, TokenInterface> $emitTokens path-keyed tokens after primitive filtering (077)
     *
     * @return array<string, string>
     */
    public function fromEmitTokens(array $emitTokens): array
    {
        $variables = [];
        foreach ($emitTokens as $path => $token) {
            $cssPath = self::EMISSION_PATH_ALIASES[$path] ?? $path;
            $variables[self::cssKeyFromPath($cssPath)] = $this->emitValue($token);
        }

        return $variables;
    }

    public static function cssKey(TokenPath $path): string
    {
        return self::cssKeyFromPath((string) $path);
    }

    private static function cssKeyFromPath(string $path): string
    {
        return self::PREFIX . str_replace('.', '-', $path);
    }

    private function emitValue(TokenInterface $token): string
    {
        $value = $token->value();

        if (\is_string($value)) {
            return $value;
        }

        if (\is_int($value) || \is_float($value)) {
            return self::formatNumber((float) $value);
        }

        if (\is_array($value) && ($value['colorSpace'] ?? null) === 'oklch') {
            return $this->emitOklch($token->path(), $value);
        }

        throw new InvalidArgumentException(sprintf(
            'Cannot emit CSS for token "%s" with non-emittable value.',
            (string) $token->path(),
        ));
    }

    /**
     * @param array<string, mixed> $value
     */
    private function emitOklch(TokenPath $path, array $value): string
    {
        $components = $value['components'] ?? null;
        if (!\is_array($components) || \count($components) < 3) {
            throw new InvalidArgumentException(sprintf(
                'OKLCH token "%s" requires three components.',
                (string) $path,
            ));
        }

        $alpha = \array_key_exists('alpha', $value) && (\is_int($value['alpha']) || \is_float($value['alpha']))
            ? (float) $value['alpha']
            : null;

        return $this->colorSpace->toCss(new OklchTuple(
            (float) $components[0],
            (float) $components[1],
            (float) $components[2],
            $alpha,
        ));
    }

    private static function formatNumber(float $value): string
    {
        $formatted = rtrim(rtrim(sprintf('%.4f', $value), '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }
}
