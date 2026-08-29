<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Dtcg\Exception;

final class UnknownDesignSystemException extends \InvalidArgumentException
{
    public function __construct(string $designSystemId, string $registryDirectory)
    {
        parent::__construct(sprintf(
            'Unknown design system id "%s"; expected a file under "%s".',
            $designSystemId,
            $registryDirectory,
        ));
    }
}
