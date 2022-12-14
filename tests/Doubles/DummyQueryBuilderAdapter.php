<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\TestsDoubles;

use Apfelfrisch\QueryFilter\Conditions\OrWhereCondition;
use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereInCondition;
use Apfelfrisch\QueryFilter\QueryBuilder;

final class DummyQueryBuilderAdapter implements QueryBuilder
{
    private array $whereConditions = [];

    private array $whereInConditions = [];

    private array $sortConditions = [];

    public function where(WhereCondition|OrWhereCondition  ...$wheres): self
    {
        $this->whereConditions = array_merge($this->whereConditions, $wheres);

        return $this;
    }

    public function whereIn(WhereInCondition $where): self
    {
        $this->whereInConditions[] = $where;

        return $this;
    }

    public function sort(string $field, SortDirection $sortDirection): self
    {
        $this->sortConditions[$field] = $sortDirection;

        return $this;
    }

    public function get(): mixed
    {
    }

    public function getAllConditions(): array
    {
        return array_merge(
            $this->whereConditions,
            $this->whereInConditions,
            $this->sortConditions,
        );
    }

    public function getCondition(string $condition): array
    {
        return $this->$condition;
    }
}
