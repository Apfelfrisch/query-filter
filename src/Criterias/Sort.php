<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Criterias;

use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\QueryBuilder;

final class Sort implements Criteria
{
    public function __construct(
        private string $name,
        private SortDirection $sortDirection = SortDirection::Ascending,
    ) {
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

    public function getType(): Type
    {
        return Type::Sort;
    }

    public function apply(QueryBuilder $builder): QueryBuilder
    {
        if (strlen($this->name) === 0) {
            return $builder;
        }

        return $builder->sort($this->name, $this->sortDirection);
    }
}
