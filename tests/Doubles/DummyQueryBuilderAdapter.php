<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Doubles;

use Apfelfrisch\QueryFilter\Conditions\OrWhereCondition;
use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereInCondition;
use Apfelfrisch\QueryFilter\QueryBuilder;

final class DummyQueryBuilderAdapter implements QueryBuilder
{
    private array $selectConditions = [];

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

    public function sort(string $column, SortDirection $sortDirection): self
    {
        $this->sortConditions[$column] = $sortDirection;

        return $this;
    }

    public function select(string ...$selects): self
    {
        $this->selectConditions[] = $selects;

        return $this;
    }

    public function builder(): mixed
    {
        return null;
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
