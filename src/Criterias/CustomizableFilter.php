<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Criterias;

use Apfelfrisch\QueryFilter\Conditions\OrWhereCondition;
use Apfelfrisch\QueryFilter\Conditions\SortCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\QueryBuilder;

final class CustomizableFilter implements Criteria
{
    /** @var array<int, WhereCondition|OrWhereCondition|SortCondition> */
    private array $conditions;

    public function __construct(
        private string $name,
        WhereCondition|OrWhereCondition|SortCondition ...$conditions
    ) {
        $this->conditions = array_values($conditions);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): Type
    {
        return Type::Filter;
    }

    public function apply(QueryBuilder $builder): QueryBuilder
    {
        $builder->where(
            ...array_filter($this->conditions, function (WhereCondition|OrWhereCondition|SortCondition $condition) {
                return ! $condition instanceof SortCondition;
            })
        );

        $sorts = array_filter($this->conditions, function (WhereCondition|OrWhereCondition|SortCondition $condition) {
            return $condition instanceof SortCondition;
        });

        foreach ($sorts as $sort) {
            $builder->sort($sort->name, $sort->sortDirection);
        }

        return $builder;
    }
}
