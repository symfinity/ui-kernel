<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Css;

use Symfinity\UiKernel\Theme\PhysicsId;
use Symfinity\UiKernel\Theme\PhysicsRegistry;

/**
 * Emits {@code [data-ui-physics="…"]} token blocks with bridge aliases (111).
 */
final class PhysicsCssEmitter
{
    public function __construct(
        private readonly PhysicsRegistry $physicsRegistry = new PhysicsRegistry(),
    ) {
    }

    public function emit(): string
    {
        $blocks = [];

        foreach (PhysicsId::cases() as $id) {
            $lines = [sprintf('[data-ui-physics="%s"] {', $id->value)];

            foreach ($this->physicsRegistry->tokensFor($id) as $key => $value) {
                $lines[] = sprintf('  %s: %s;', $key, $value);
            }

            foreach ($this->physicsRegistry->bridgeAliases($id) as $key => $value) {
                $lines[] = sprintf('  %s: %s;', $key, $value);
            }

            $lines[] = '}';
            $blocks[] = implode("\n", $lines);
        }

        return implode("\n\n", $blocks);
    }
}
