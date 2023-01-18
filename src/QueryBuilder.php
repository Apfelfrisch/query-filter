<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter;

use Apfelfrisch\QueryFilter\Conditions\OrWhereCondition;
use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereInCondition;

/**
 * @template T
 */
interface QueryBuilder
{
    /** @return self<T> */
    public function where(WhereCondition|OrWhereCondition ...$wheres): self;

    /** @return self<T> */
    public function whereIn(WhereInCondition $where): self;

    /** @return self<T> */
    public function sort(string $field, SortDirection $sortDirection): self;

    /** @return T */
    public function builder(): mixed;
}
