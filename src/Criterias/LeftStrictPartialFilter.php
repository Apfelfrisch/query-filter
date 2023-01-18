<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Criterias;

final class LeftStrictPartialFilter extends AbstractPartialFilter
{
    /** @param string|array<int, string>|null $value */
    public static function new(string $name, string|array|null $value = null): self
    {
        return new self($name, $value);
    }

    protected function prepareValue(string $value): string
    {
        return "$value%";
    }
}
