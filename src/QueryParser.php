<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter;

use Apfelfrisch\QueryFilter\Criterias\CriteriaCollection;

interface QueryParser
{
    public function setQueryString(string $query): self;

    public function parse(CriteriaCollection $allowedFilters, CriteriaCollection $allowedSorts): CriteriaCollection;
}
