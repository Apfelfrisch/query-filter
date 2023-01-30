<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Criterias;

use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\OrWhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\QueryBuilder;

abstract class AbstractPartialFilter implements Filter
{
    private string $column;

    /** @param string|array<int, string>|null $value */
    public function __construct(
        private string $name,
        private string|array|null $value = null
    ) {
        $this->column = $this->name;
    }

    public function forColumn(string $column): self
    {
        $this->column = $column;

        return $this;
    }

    /** @param string|array<int, string> $value */
    public function setValue(string|array $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function apply(QueryBuilder $builder): QueryBuilder
    {
        if (null === $value = $this->value) {
            return $builder;
        }

        if (is_string($value)) {
            if (strlen($value) === 0) {
                return $builder;
            }

            return $builder->where(
                new WhereCondition($this->column, Operator::Like, $this->prepareValue($value))
            );
        }

        $filteredValues = array_filter($value, fn (string|null $value) => $value !== null && strlen($value) > 0);

        if ($filteredValues === []) {
            return $builder;
        }

        $conditions = [];

        foreach ($filteredValues as $value) {
            $conditions[] = new OrWhereCondition($this->name, Operator::Like, $this->prepareValue($value));
        }

        return $builder->where(...$conditions);
    }

    abstract protected function prepareValue(string $value): string;
}
