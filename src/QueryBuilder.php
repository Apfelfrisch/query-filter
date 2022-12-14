<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter;

use Apfelfrisch\QueryFilter\Conditions\OrWhereCondition;
use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereInCondition;

interface QueryBuilder
{
    public function where(WhereCondition|OrWhereCondition ...$wheres): self;

    public function whereIn(WhereInCondition $where): self;

    public function sort(string $field, SortDirection $sortDirection): self;

    public function get(): mixed;
}
