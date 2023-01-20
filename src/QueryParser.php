<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter;

use Apfelfrisch\QueryFilter\Criterias\Criteria;
use Apfelfrisch\QueryFilter\Criterias\Filter;
use Apfelfrisch\QueryFilter\Criterias\Sorting;

interface QueryParser
{
    public function skipForbiddenCriterias(bool $skip = true): self;

    /**
     * @param CriteriaCollection<Filter> $allowedFilters
     * @param CriteriaCollection<Sorting> $allowedSorts
     * @return CriteriaCollection<Criteria>
     */
    public function parse(
        QueryBag $query,
        CriteriaCollection $allowedFilters,
        CriteriaCollection $allowedSorts,
    ): CriteriaCollection;
}
