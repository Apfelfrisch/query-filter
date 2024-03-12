<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Criterias;

use Apfelfrisch\QueryFilter\Criterias\AbstractPartialFilter;
use Apfelfrisch\QueryFilter\Criterias\LeftStrictPartialFilter;

final class LeftStrictPartialFilterTest extends AbstractPartialFilterTestCase
{
    protected function prepareValue(string $value): string
    {
        return "$value%";
    }

    protected function buildFilter(...$values): AbstractPartialFilter
    {
        return LeftStrictPartialFilter::new(...$values);
    }
}
