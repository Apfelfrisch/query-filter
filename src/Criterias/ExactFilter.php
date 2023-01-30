<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Criterias;

use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereInCondition;
use Apfelfrisch\QueryFilter\QueryBuilder;

final class ExactFilter implements Filter
{
    private string $column;

    /** @param string|array<int, string|null>|null $value */
    public function __construct(
        private string $name,
        private string|array|null $value = null
    ) {
        $this->column = $this->name;
    }

    /** @param string|array<int, string|null>|null $value */
    public static function new(string $name, string|array|null $value = null): self
    {
        return new self($name, $value);
    }

    public function forColumn(string $column): self
    {
        $this->column = $column;

        return $this;
    }

    /** @param string|array<int, string|null> $value */
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
        if ($this->value === []) {
            return $builder;
        }

        $values = $this->value;

        if (! is_array($values)) {
            $values = [$values];
        }

        // Dont use WhereInCondition if there is a null value
        if (count($values) > 1 && ! in_array(null, $values, true)) {
            return $builder->whereIn(new WhereInCondition($this->column, $values));
        }

        $conditions = [];

        foreach ($values as $value) {
            $conditions[] = new WhereCondition($this->column, Operator::Equal, $value);
        }

        return $builder->where(...$conditions);
    }
}
