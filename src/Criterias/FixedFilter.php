<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Criterias;

use Apfelfrisch\QueryFilter\Conditions\OrWhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\QueryBuilder;

final class FixedFilter implements Filter
{
    /** @var array<int, WhereCondition|OrWhereCondition> */
    private array $conditions;

    public function __construct(
        private string $name,
        WhereCondition|OrWhereCondition ...$conditions
    ) {
        $this->conditions = array_values($conditions);
    }

    public function setValue(string|array $value): self
    {
        // Filter ist Fixed, so setValue has no effect
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function apply(QueryBuilder $builder): QueryBuilder
    {
        $builder->where(...$this->conditions);

        return $builder;
    }
}
