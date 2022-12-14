<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Criterias;

use Apfelfrisch\QueryFilter\Criterias\AbstractPartialFilter;
use Apfelfrisch\QueryFilter\Criterias\LeftStrictPartialFilter;

final class LeftStrictPartialFilterTest extends AbstractPartialFilterTest
{
    protected function prepareValue(string $value): string
    {
        return "$value%";
    }

    protected function buildFilter(...$values): AbstractPartialFilter
    {
        return new LeftStrictPartialFilter(...$values);
    }
}
