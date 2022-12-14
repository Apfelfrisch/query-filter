<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Criterias;

final class LeftStrictPartialFilter extends AbstractPartialFilter
{
    protected function prepareValue(string $value): string
    {
        return "$value%";
    }
}
