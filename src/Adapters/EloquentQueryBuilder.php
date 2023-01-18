<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Adapters;

use Apfelfrisch\QueryFilter\Conditions\OrWhereCondition;
use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereInCondition;
use Apfelfrisch\QueryFilter\QueryBuilder;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Database\Query\Builder;

/**
 * @implements QueryBuilder<Builder|EloquentBuilder>
 */
final class EloquentQueryBuilder implements QueryBuilder
{
    public function __construct(
        private Builder|EloquentBuilder $builder
    ) {
    }

    public function where(WhereCondition|OrWhereCondition ...$wheres): self
    {
        $this->builder->where(function ($builder) use ($wheres) {
            foreach ($wheres as $where) {
                if ($where instanceof OrWhereCondition) {
                    $builder->orWhere($where->field, $where->operator->value, $where->value);
                } else {
                    $builder->where($where->field, $where->operator->value, $where->value);
                }
            }
        });

        return $this;
    }

    public function whereIn(WhereInCondition $where): self
    {
        $this->builder->whereIn($where->field, $where->values);

        return $this;
    }

    public function sort(string $field, SortDirection $sortDirection): self
    {
        $this->builder->orderBy($field, $sortDirection->value);

        return $this;
    }

    public function builder(): Builder|EloquentBuilder
    {
        return $this->builder;
    }
}
