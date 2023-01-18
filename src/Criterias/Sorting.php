<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Criterias;

use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\QueryBuilder;

final class Sorting implements Criteria
{
    private string $field;

    public function __construct(
        private string $name,
        private SortDirection $sortDirection = SortDirection::Ascending,
    ) {
        $this->field = $this->name;
    }

    public function forField(string $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function setSortDirection(SortDirection $sortDirection): void
    {
        $this->sortDirection = $sortDirection;
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
        if (strlen($this->field) === 0) {
            return $builder;
        }

        return $builder->sort($this->field, $this->sortDirection);
    }
}
