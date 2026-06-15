<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Css;

use Symfinity\UiKernel\Contract\Emitter\AtRulesContributorInterface;
use Symfinity\UiKernel\Contract\Resolver\ResolvedGraphInterface;
use Symfinity\UiKernel\Dtcg\AtRulesContributor;
use Symfinity\UiKernel\Dtcg\ProfileGlobalsLayerRegistry;
use Symfinity\UiKernel\Theme\Theme;
use Symfinity\UiKernel\Theme\ThemeLineageCatalog;
use Symfinity\UiKernel\Profile\SystemProfile;
use Symfinity\UiKernel\Profile\SystemProfileRegistry;
use Symfinity\UiKernel\Token\DesignTokenSet;
use Symfinity\UiKernel\Token\SemanticColorDerivatives;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class CssGenerator
{
    public function __construct(
        private readonly ?SystemProfileRegistry $profileRegistry = null,
        private readonly SemanticColorDerivatives $semanticColorDerivatives = new SemanticColorDerivatives(),
        private readonly CssVariableSet $cssVariableSet = new CssVariableSet(),
        private readonly ?AtRulesContributorInterface $atRulesContributor = null,
        private readonly ?ProfileGlobalsLayerRegistry $profileGlobalsLayerRegistry = null,
    ) {
    }

    /**
     * Emit `--ui-*` CSS from a resolved DTCG graph behind the existing token API (076 US1).
     *
     * Bridges the graph onto a {@see DesignTokenSet} and reuses {@see forResolvedTokens()},
     * preserving output parity with {@see forTheme()} for built-in themes.
     */
    public function forResolvedGraph(
        ResolvedGraphInterface $graph,
        string $themeId,
        ?string $schemaVersion = null,
        ?SystemProfile $profile = null,
        bool $scrollMotion = false,
    ): string {
        $schemaVersion ??= ThemeTokenSchema::V1_0;
        $tokens = new DesignTokenSet($this->cssVariableSet->fromResolvedGraph($graph), $schemaVersion);

        return $this->forResolvedTokens($themeId, $tokens, $schemaVersion, $profile, $scrollMotion, $graph);
    }

    public function forTheme(Theme $theme, ?string $schemaVersion = null): string
    {
        $schemaVersion ??= $theme->schemaVersion();

        return $this->forResolvedTokens(
            $theme->id(),
            $theme->tokens(),
            $schemaVersion,
            $this->resolveProfile(),
            $theme->scrollMotion(),
        );
    }

    public function forResolvedTokens(
        string $themeId,
        DesignTokenSet $tokens,
        ?string $schemaVersion = null,
        ?SystemProfile $profile = null,
        bool $scrollMotion = false,
        ?ResolvedGraphInterface $graph = null,
    ): string {
        $schemaVersion ??= $tokens->schemaVersion();
        $profile ??= $this->resolveProfile();
        $lines = [];
        $selector = sprintf('[data-theme="%s"]', $themeId);
        $lines[] = sprintf('/* ui-kernel schema:%s profile:%s */', $schemaVersion, $profile->id);
        $lines[] = $selector . ' {';
        $lines[] = sprintf('  color-scheme: %s;', ThemeLineageCatalog::nativeColorScheme($themeId));
        $lines[] = '  color: var(--ui-color-text);';

        foreach ($tokens->all() as $key => $value) {
            $lines[] = sprintf('  %s: %s;', $key, $value);
        }

        $lines[] = '}';

        $tokenMap = $tokens->all();

        if ($themeId === 'default') {
            $lines[] = ':root {';
            $lines[] = sprintf('  color-scheme: %s;', ThemeLineageCatalog::nativeColorScheme($themeId));
            $lines[] = '  color: var(--ui-color-text);';
            foreach ($tokenMap as $key => $value) {
                $lines[] = sprintf('  %s: %s;', $key, $value);
            }
            $lines[] = '}';
        }

        $selectors = $themeId === 'default' ? [$selector, ':root'] : [$selector];
        $lines = [...$lines, ...$this->p3GamutOverrides($selectors, $tokenMap)];

        $lines[] = $this->profileGlobalsCss($graph);

        return implode("\n", $lines);
    }

    /**
     * Light/dark token swap via prefers-color-scheme only — no JS.
     */
    public function forAdaptiveThemePair(Theme $light, Theme $dark): string
    {
        if ($light->schemaVersion() !== $dark->schemaVersion()) {
            throw new \InvalidArgumentException(sprintf(
                'Adaptive pair schema mismatch: %s vs %s',
                $light->schemaVersion(),
                $dark->schemaVersion(),
            ));
        }

        $schemaVersion = $light->schemaVersion();
        $profile = $this->resolveProfile();
        $anchorId = $light->id();
        $selector = sprintf('html[data-theme="%s"]', $anchorId);
        $lines = [];
        $lines[] = sprintf('/* ui-kernel adaptive:%s+%s schema:%s profile:%s */', $anchorId, $dark->id(), $schemaVersion, $profile->id);
        $lines[] = $selector . ' {';
        $lines[] = '  color-scheme: light dark;';

        foreach ($light->tokens()->all() as $key => $value) {
            $lines[] = sprintf('  %s: %s;', $key, $value);
        }

        $lines[] = '}';

        if ($anchorId === 'default') {
            $lines[] = ':root {';
            foreach ($light->tokens()->all() as $key => $value) {
                $lines[] = sprintf('  %s: %s;', $key, $value);
            }
            $lines[] = '}';
        }

        $lines[] = '@media (prefers-color-scheme: dark) {';
        $lines[] = '  ' . $selector . ' {';

        foreach ($dark->tokens()->all() as $key => $value) {
            $lines[] = sprintf('    %s: %s;', $key, $value);
        }

        $lines[] = '  }';

        if ($anchorId === 'default') {
            $lines[] = '  :root {';
            foreach ($dark->tokens()->all() as $key => $value) {
                $lines[] = sprintf('    %s: %s;', $key, $value);
            }
            $lines[] = '  }';
        }

        $darkSelectors = $anchorId === 'default' ? [$selector, ':root'] : [$selector];
        $lines = [...$lines, ...$this->p3GamutOverrides($darkSelectors, $dark->tokens()->all(), '  ')];

        $lines[] = '}';

        $lightSelectors = $anchorId === 'default' ? [$selector, ':root'] : [$selector];
        $lines = [...$lines, ...$this->p3GamutOverrides($lightSelectors, $light->tokens()->all())];

        $lines[] = $this->profileGlobalsCss(null);

        return implode("\n", $lines);
    }

    /**
     * @param list<string>          $selectors
     * @param array<string, string> $tokens
     *
     * @return list<string>
     */
    private function p3GamutOverrides(array $selectors, array $tokens, string $indent = ''): array
    {
        $boosts = $this->semanticColorDerivatives->p3Boosts($tokens);
        if ($boosts === []) {
            return [];
        }

        $lines = [$indent . '@media (color-gamut: p3) {'];
        foreach ($selectors as $selector) {
            $lines[] = $indent . '  ' . $selector . ' {';
            foreach ($boosts as $boost) {
                $lines[] = sprintf('%s    %s: %s;', $indent, $boost['key'], $boost['css']);
            }
            $lines[] = $indent . '  }';
        }
        $lines[] = $indent . '}';

        return $lines;
    }

    /**
     * Cache pool key fragment when Symfony cache is enabled (css-generation contract).
     *
     * @return array{
     *     themeId: string,
     *     userTokenHash: string,
     *     schemaVersion: string,
     *     presetHash: string,
     *     roleRulesVersion: string,
     *     systemProfileId: string,
     *     profileHash: string,
     *     layerSignature: string,
     *     profileGlobalsRevision: string
     * }
     */
    public static function cacheKeyParts(
        string $themeId,
        string $userTokenHash,
        string $schemaVersion,
        SystemProfile $profile,
        string $presetHash = '',
        string $layerSignature = '',
        string $profileGlobalsRevision = '',
    ): array {
        return CssCacheKeyPolicy::parts(
            $themeId,
            $userTokenHash,
            $schemaVersion,
            $presetHash,
            $profile,
            $layerSignature,
            $profileGlobalsRevision,
        );
    }

    private function resolveProfile(): SystemProfile
    {
        return $this->profileRegistry?->resolve() ?? SystemProfile::defaultProfile();
    }

    private function profileGlobalsCss(?ResolvedGraphInterface $graph = null): string
    {
        $globalsGraph = $this->resolveProfileGlobalsGraph($graph);

        return $this->atRulesContributor()->emitZIndexVars($globalsGraph)
            . "\n"
            . $this->atRulesContributor()->contribute($globalsGraph);
    }

    private function atRulesContributor(): AtRulesContributorInterface
    {
        return $this->atRulesContributor ?? new AtRulesContributor();
    }

    private function profileGlobalsRegistry(): ProfileGlobalsLayerRegistry
    {
        return $this->profileGlobalsLayerRegistry ?? ProfileGlobalsLayerRegistry::fromDefaultPath();
    }

    private function resolveProfileGlobalsGraph(?ResolvedGraphInterface $graph): ResolvedGraphInterface
    {
        if ($graph !== null && $this->graphHasProfileGlobals($graph)) {
            return $graph;
        }

        return $this->profileGlobalsRegistry()->resolvedGraph();
    }

    private function graphHasProfileGlobals(ResolvedGraphInterface $graph): bool
    {
        foreach ($graph->all() as $path => $token) {
            if (str_starts_with($path, 'zIndex.') || str_starts_with($path, 'keyframes.')) {
                return true;
            }
        }

        return false;
    }
}
