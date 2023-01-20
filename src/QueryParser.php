<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter;

interface QueryParser
{
    public function parse(
        QueryBag $query,
        CriteriaCollection $allowedFilters,
        CriteriaCollection $allowedSorts,
    ): CriteriaCollection;
}
