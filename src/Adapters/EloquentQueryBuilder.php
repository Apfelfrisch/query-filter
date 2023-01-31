<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Adapters;

use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\OrWhereCondition;
use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereInCondition;
use Apfelfrisch\QueryFilter\Exceptions\ConditionException;
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

    public function select(string ...$selects): self
    {
        // Reset `SELECT *`
        if (count($this->builder->columns ?? []) === 1 && strval($this->builder->columns[0]) === '*') {
            $this->builder->columns = [];
        }

        $this->builder->selectRaw(implode(', ', $selects));

        return $this;
    }

    public function where(WhereCondition|OrWhereCondition ...$wheres): self
    {
        $this->builder->where(function ($builder) use ($wheres) {
            foreach ($wheres as $where) {
                if ($where instanceof OrWhereCondition) {
                    $builder->orWhereRaw($this->buildRawString($where), $where->value);
                } else {
                    $builder->whereRaw($this->buildRawString($where), $where->value);
                }
            }
        });

        return $this;
    }

    public function whereIn(WhereInCondition $where): self
    {
        $placeholder = implode(', ', array_map(fn (): string => '?', $where->values));

        $this->builder->whereRaw("$where->column in ($placeholder)", $where->values);

        return $this;
    }

    public function sort(string $column, SortDirection $sortDirection): self
    {
        $this->builder->orderByRaw("$column $sortDirection->value");

        return $this;
    }

    public function builder(): Builder|EloquentBuilder
    {
        return $this->builder;
    }

    private function buildRawString(WhereCondition|OrWhereCondition $where): string
    {
        if ($where->value === null) {
            return match ($where->operator) {
                Operator::Equal => "$where->column is null",
                Operator::NotEqual => "$where->column is not null",
                default => throw ConditionException::invalidOperatorForNullableField($where->operator),
            };
        }

        return "$where->column {$where->operator->value} ?";
    }
}
