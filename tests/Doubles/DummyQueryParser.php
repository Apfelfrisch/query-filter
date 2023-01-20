<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\TestsDoubles;

use Apfelfrisch\QueryFilter\CriteriaCollection;
use Apfelfrisch\QueryFilter\QueryBag;
use Apfelfrisch\QueryFilter\QueryParser;

final class DummyQueryParser implements QueryParser
{
    public bool $skipForbiddenCriterias = false;
    public QueryBag|null $query = null;
    public CriteriaCollection|null $allowedFilters = null;
    public CriteriaCollection|null $allowedSorts = null;

    public function skipForbiddenCriterias(bool $skip = true): self
    {
        $this->skipForbiddenCriterias = $skip;

        return $this;
    }

    public function parse(
        QueryBag $query,
        CriteriaCollection $allowedFilters,
        CriteriaCollection $allowedSorts
    ): CriteriaCollection {
        $this->query = $query;

        $this->allowedFilters = $allowedFilters;
        $this->allowedSorts = $allowedSorts;

        return new CriteriaCollection();
    }
}
