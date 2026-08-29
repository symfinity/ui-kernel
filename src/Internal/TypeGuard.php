<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Internal;

final class TypeGuard
{
    /**
     * @param array<mixed, mixed> $array
     *
     * @return array<string, mixed>
     */
    public static function stringKeyMap(array $array): array
    {
        foreach ($array as $key => $_) {
            if (!is_string($key)) {
                throw new \InvalidArgumentException('Expected string-keyed array.');
            }
        }

        /** @var array<string, mixed> $array */
        return $array;
    }

    public static function numericFloat(mixed $value): float
    {
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException('Expected numeric value.');
        }

        return (float) $value;
    }

    public static function string(mixed $value, string $message = 'Expected string value.'): string
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException($message);
        }

        return $value;
    }
}
