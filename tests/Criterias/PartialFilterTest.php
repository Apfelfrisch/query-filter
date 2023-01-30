<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Criterias;

use Apfelfrisch\QueryFilter\Criterias\AbstractPartialFilter;
use Apfelfrisch\QueryFilter\Criterias\PartialFilter;

final class PartialFilterTest extends AbstractPartialFilterTest
{
    protected function prepareValue(string $value): string
    {
        return "%$value%";
    }

    protected function buildFilter(...$values): AbstractPartialFilter
    {
        return PartialFilter::new(...$values);
    }
}
