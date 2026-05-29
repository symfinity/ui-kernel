<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Flavour;

use InvalidArgumentException;

final class FlavourRegistry
{
    /** @var array<string, Flavour> */
    private array $flavours = [];

    public function __construct()
    {
        foreach (FlavourCatalog::all() as $flavour) {
            $this->register($flavour);
        }
    }

    public function register(Flavour $flavour): void
    {
        $id = $flavour->id();
        if (isset($this->flavours[$id])) {
            throw new InvalidArgumentException(sprintf('Duplicate flavour id "%s".', $id));
        }

        $this->flavours[$id] = $flavour;
    }

    public function get(string $id): Flavour
    {
        if (!isset($this->flavours[$id])) {
            throw new InvalidArgumentException(sprintf('Unknown flavour "%s".', $id));
        }

        return $this->flavours[$id];
    }

    public function resolve(?string $id): Flavour
    {
        if ($id === null || $id === '') {
            return $this->get('default');
        }

        if (!isset($this->flavours[$id])) {
            return $this->get('default');
        }

        return $this->flavours[$id];
    }

    /**
     * @return list<string>
     */
    public function ids(): array
    {
        return array_keys($this->flavours);
    }

    /**
     * @return list<Flavour>
     */
    public function all(): array
    {
        return array_values($this->flavours);
    }
}
