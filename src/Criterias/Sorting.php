<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Criterias;

use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\QueryBuilder;

final class Sorting implements Criteria
{
    private string $column;

    public function __construct(
        private string $name,
        private SortDirection $sortDirection = SortDirection::Ascending,
    ) {
        $this->column = $this->name;
    }

    public static function new(string $name, SortDirection $sortDirection = SortDirection::Ascending): self
    {
        return new self($name, $sortDirection);
    }

    public function forColumn(string $column): self
    {
        $this->column = $column;

        return $this;
    }

    public function setSortDirection(SortDirection $sortDirection): self
    {
        $this->sortDirection = $sortDirection;

        return $this;
    }

    public function getSortDirection(): SortDirection
    {
        return $this->sortDirection;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function apply(QueryBuilder $builder): QueryBuilder
    {
        if (strlen($this->column) === 0) {
            return $builder;
        }

        return $builder->sort($this->column, $this->sortDirection);
    }
}
