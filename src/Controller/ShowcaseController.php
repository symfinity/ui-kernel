<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Controller;

use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Flavour\FlavourRegistry;
use Symfinity\UiKernel\Renderer\HtmlRenderer;
use Symfinity\UiKernel\Showcase\ShowcasePageFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Twig\Environment;

#[AsController]
final class ShowcaseController
{
    /** @var list<string> */
    public const CAROUSEL_ORDER = [
        'default',
        'dark',
        'semantic',
        'semantic-dark',
        'utility',
        'utility-dark',
    ];

    public function __construct(
        private readonly Environment $twig,
        private readonly ShowcasePageFactory $pageFactory,
        private readonly HtmlRenderer $htmlRenderer,
        private readonly FlavourRegistry $flavourRegistry,
        private readonly CssGenerator $cssGenerator,
    ) {
    }

    public function show(Request $request): Response
    {
        $themeQuery = $request->query->getString('theme');
        $themeId = $themeQuery !== '' ? $themeQuery : null;
        $flavour = $this->flavourRegistry->resolve($themeId);
        $carousel = $this->resolveCarousel($themeId, $request->query->get('carousel'));

        $page = $this->pageFactory->create();

        $inlineCss = $this->cssForShowcase();

        return new Response($this->twig->render('@UiKernel/showcase.html.twig', [
            'pageHtml' => $this->htmlRenderer->render($page),
            'themeId' => $flavour->id(),
            'themeLabel' => $flavour->label(),
            'carousel' => $carousel,
            'carouselOrder' => self::CAROUSEL_ORDER,
            'inlineCss' => $inlineCss,
            'disclaimer' => 'Symfinity token packs only — Semantic and Utility lineages echo common design conventions, not third-party products.',
        ]));
    }

    public function themeCss(Request $request): Response
    {
        $flavour = $this->flavourRegistry->resolve(
            $request->query->getString('theme') !== '' ? $request->query->getString('theme') : null,
        );

        return new Response(
            $this->cssGenerator->forFlavour($flavour),
            Response::HTTP_OK,
            ['Content-Type' => 'text/css; charset=UTF-8'],
        );
    }

    public function showcaseScript(): Response
    {
        $path = \dirname(__DIR__, 2) . '/assets/showcase.js';
        $content = is_readable($path) ? (string) file_get_contents($path) : '';

        return new Response(
            $content,
            Response::HTTP_OK,
            ['Content-Type' => 'application/javascript; charset=UTF-8'],
        );
    }

    private function cssForShowcase(): string
    {
        $chunks = [];
        foreach (self::CAROUSEL_ORDER as $id) {
            $chunks[] = $this->cssGenerator->forFlavour($this->flavourRegistry->get($id));
        }

        return implode("\n", $chunks);
    }

    private function resolveCarousel(?string $fixedThemeId, mixed $carouselQuery): bool
    {
        if ($fixedThemeId !== null && $fixedThemeId !== '') {
            if ($carouselQuery === '1' || $carouselQuery === 1) {
                return true;
            }

            return false;
        }

        if ($carouselQuery === '0' || $carouselQuery === 0) {
            return false;
        }

        return true;
    }
}
