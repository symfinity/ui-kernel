<?php

declare(strict_types=1);

namespace Symfony\Bundle\TwigBundle\DependencyInjection\Configurator;

if (!class_exists(TwigConfigurator::class, false)) {
    class TwigConfigurator
    {
        public function path(string $path, ?string $namespace = null): static
        {
            return $this;
        }
    }
}
