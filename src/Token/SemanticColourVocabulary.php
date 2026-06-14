<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use Symfinity\UiKernel\Contract\Resolver\ResolvedGraphInterface;
use Symfinity\UiKernel\Dtcg\DesignSystemLayerRegistry;
use Symfinity\UiKernel\Dtcg\LayerStackBuilder;
use Symfinity\UiKernel\Dtcg\ThemeDtcgResolver;
use Symfinity\UiKernel\Theme\ThemeCatalog;

/**
 * Graph-derived semantic colour names for {@code data-ui-variant} (077 SSOT).
 */
final class SemanticColourVocabulary
{
    /** @var list<string> Platform chameleon minimum — parity with former enum set. */
    public const PLATFORM_MINIMUM = [
        'primary',
        'secondary',
        'tertiary',
        'success',
        'danger',
        'info',
        'warning',
        'ghost',
    ];

    /** @var list<string> Non-variant infrastructure tokens under {@code color.*}. */
    private const INFRASTRUCTURE_COLOURS = [
        'overlay',
        'focus',
    ];

    public function __construct(
        private readonly ResolvedGraphInterface $graph,
    ) {
    }

    public static function fromBuiltInThemeId(string $themeId): self
    {
        $resolver = new ThemeDtcgResolver(
            new LayerStackBuilder(
                new DesignSystemLayerRegistry(DesignSystemLayerRegistry::defaultDirectory()),
            ),
        );

        return new self($resolver->resolvedGraph(ThemeCatalog::variant($themeId)));
    }

    public static function fromGraph(ResolvedGraphInterface $graph): self
    {
        return new self($graph);
    }

    /**
     * @return list<string>
     */
    public function all(): array
    {
        $available = [];
        foreach ($this->graph->semanticColors() as $name) {
            if (\in_array($name, self::INFRASTRUCTURE_COLOURS, true)) {
                continue;
            }
            $available[$name] = true;
        }

        $ordered = [];
        foreach (self::PLATFORM_MINIMUM as $name) {
            if (isset($available[$name])) {
                $ordered[] = $name;
                unset($available[$name]);
            }
        }

        $extras = array_keys($available);
        sort($extras);

        return [...$ordered, ...$extras];
    }

    public function contains(string $name): bool
    {
        return \in_array($name, $this->all(), true);
    }

    public function defaultName(): string
    {
        if ($this->contains('primary')) {
            return 'primary';
        }

        $all = $this->all();

        return $all[0] ?? 'primary';
    }

    public function graph(): ResolvedGraphInterface
    {
        return $this->graph;
    }
}
