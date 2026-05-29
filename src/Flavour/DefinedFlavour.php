<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Flavour;

use InvalidArgumentException;
use Symfinity\UiKernel\Token\DesignTokenSet;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final readonly class DefinedFlavour implements Flavour
{
    private DesignTokenSet $tokenSet;

    /**
     * @param array<string, string> $colors Map of ThemeTokenSchema::COLOR_KEYS only
     */
    public function __construct(
        private string $id,
        private string $label,
        LayoutProfile $layout,
        array $colors,
    ) {
        foreach (ThemeTokenSchema::COLOR_KEYS as $key) {
            if (!isset($colors[$key]) || $colors[$key] === '') {
                throw new InvalidArgumentException(sprintf('Flavour "%s" is missing color token "%s".', $id, $key));
            }
        }

        $unknown = array_diff(array_keys($colors), ThemeTokenSchema::COLOR_KEYS);
        if ($unknown !== []) {
            throw new InvalidArgumentException(sprintf(
                'Flavour "%s" has unknown color keys: %s',
                $id,
                implode(', ', $unknown),
            ));
        }

        $this->tokenSet = DesignTokenSet::fromArray([...$layout->layout(), ...$colors]);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function tokens(): DesignTokenSet
    {
        return $this->tokenSet;
    }
}
