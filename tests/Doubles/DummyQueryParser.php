<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\TestsDoubles;

use Apfelfrisch\QueryFilter\Criterias\CriteriaCollection;
use Apfelfrisch\QueryFilter\QueryParser;

final class DummyQueryParser implements QueryParser
{
    public string|null $queryStrig = null;
    public CriteriaCollection|null $allowedFilters = null;
    public CriteriaCollection|null $allowedSorts = null;

    public function setQueryString(string $query): self
    {
        $this->queryStrig = $query;

        return $this;
    }

    public function parse(CriteriaCollection $allowedFilters, CriteriaCollection $allowedSorts): CriteriaCollection
    {
        $this->allowedFilters = $allowedFilters;
        $this->allowedSorts = $allowedSorts;

        return new CriteriaCollection();
    }
}
