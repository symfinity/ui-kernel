<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Theme;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Contract\Layer\LayerRole;
use Symfinity\UiKernel\Contract\Layer\LayerStack;
use Symfinity\UiKernel\Contract\Layer\TokenLayer;
use Symfinity\UiKernel\Contract\Layer\TokenLayerInterface;
use Symfinity\UiKernel\Contract\Token\AliasReference;
use Symfinity\UiKernel\Contract\Token\Token;
use Symfinity\UiKernel\Contract\Token\TokenPath;
use Symfinity\UiKernel\Contract\Token\TokenType;
use Symfinity\UiKernel\Dtcg\LayeredTokenResolver;
use Symfinity\UiKernel\Theme\DefinedTheme;
use Symfinity\UiKernel\Theme\ThemeCatalog;

/**
 * US4 (076 FR-009 / SC-004): a theme references a non-default design system and inherits
 * its semantic vocabulary while supplying value overrides (additive overlay v1).
 */
final class DesignSystemReferenceTest extends TestCase
{
    private function token(string $path, TokenType $type, mixed $value): Token
    {
        $value = \is_string($value) && AliasReference::isAlias($value) ? AliasReference::parse($value) : $value;

        return new Token(TokenPath::fromString($path), $type, $value);
    }

    #[Test]
    public function twoThemesShareDesignSystemSemanticNamesAndDifferOnlyInValues(): void
    {
        $base = new TokenLayer('base', LayerRole::Base, [
            'color.text' => $this->token('color.text', TokenType::Color, 'oklch(0.2 0 0)'),
        ]);

        $corp = new TokenLayer('corp', LayerRole::DesignSystem, [
            'color.brand' => $this->token('color.brand', TokenType::Color, 'oklch(0.5 0.1 250)'),
            'color.accent' => $this->token('color.accent', TokenType::Color, 'oklch(0.6 0.12 30)'),
        ]);

        $themeA = new TokenLayer('a', LayerRole::Theme, [
            'color.brand' => $this->token('color.brand', TokenType::Color, 'oklch(0.55 0.18 250)'),
        ]);
        $themeB = new TokenLayer('b', LayerRole::Theme, [
            'color.brand' => $this->token('color.brand', TokenType::Color, 'oklch(0.45 0.2 250)'),
        ]);

        $resolver = new LayeredTokenResolver();
        $graphA = $resolver->resolve(new LayerStack($base, $corp, $themeA));
        $graphB = $resolver->resolve(new LayerStack($base, $corp, $themeB));

        // Both share the design system's semantic vocabulary.
        self::assertContains('brand', $graphA->semanticColors());
        self::assertContains('accent', $graphA->semanticColors());
        self::assertSame($graphA->semanticColors(), $graphB->semanticColors());

        // Differ only where the theme overrides.
        self::assertNotSame($graphA->get('color.brand')->value(), $graphB->get('color.brand')->value());
        self::assertSame($graphA->get('color.accent')->value(), $graphB->get('color.accent')->value());
    }

    #[Test]
    public function themeDesignSystemIdSelectsTheBoundDesignSystemLayer(): void
    {
        $defaultDs = new TokenLayer('platform', LayerRole::DesignSystem, []);
        $corpDs = new TokenLayer('corp', LayerRole::DesignSystem, []);
        /** @var array<string, TokenLayerInterface> $registry */
        $registry = ['platform' => $defaultDs, 'corp' => $corpDs];

        $tokens = ThemeCatalog::get('default')->tokens();
        $boundTheme = new DefinedTheme('corp-theme', 'Corp', $tokens->schemaVersion(), $tokens, false, 'corp');
        $defaultTheme = new DefinedTheme('plain', 'Plain', $tokens->schemaVersion(), $tokens);

        self::assertSame('corp', $boundTheme->designSystemId());
        self::assertNull($defaultTheme->designSystemId());

        // Resolution input selects the design-system layer by the theme's reference (FR-009).
        $selectedForBound = $registry[$boundTheme->designSystemId() ?? 'platform'];
        $selectedForDefault = $registry[$defaultTheme->designSystemId() ?? 'platform'];

        self::assertSame('corp', $selectedForBound->id());
        self::assertSame('platform', $selectedForDefault->id());
    }

    #[Test]
    public function designSystemOmittingSemanticTokenKeepsBaseDefault(): void
    {
        $base = new TokenLayer('base', LayerRole::Base, [
            'color.primary' => $this->token('color.primary', TokenType::Color, 'oklch(0.5 0.1 250)'),
        ]);
        // Theme's design system omits color.primary and only adds accent.
        $ds = new TokenLayer('corp', LayerRole::DesignSystem, [
            'color.accent' => $this->token('color.accent', TokenType::Color, 'oklch(0.6 0.12 30)'),
        ]);

        $graph = (new LayeredTokenResolver())->resolve(new LayerStack($base, $ds));

        self::assertTrue($graph->has('color.primary'), 'base default remains (additive overlay)');
        self::assertSame('oklch(0.5 0.1 250)', $graph->get('color.primary')->value());
        self::assertContains('accent', $graph->semanticColors());
    }
}
