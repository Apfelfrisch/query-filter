<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter;

use Apfelfrisch\QueryFilter\CriteriaCollection;

interface QueryParser
{
    public function setQuery(QueryBag $query): self;

    public function parse(CriteriaCollection $allowedFilters, CriteriaCollection $allowedSorts): CriteriaCollection;
}
