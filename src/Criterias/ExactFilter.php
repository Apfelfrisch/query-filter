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

    /** @param string|array<int, string>|null $value */
    public function __construct(
        private string $name,
        private string|array|null $value = null
    ) {
        $this->column = $this->name;
    }

    /** @param string|array<int, string>|null $value */
    public static function new(string $name, string|array|null $value = null): self
    {
        return new self($name, $value);
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
            $value = [$value];
        }

        $filteredValues = array_filter($value, strlen(...));

        if ($filteredValues === []) {
            return $builder;
        }

        if (count($filteredValues) === 1) {
            return $builder->where(new WhereCondition($this->column, Operator::Equal, current($filteredValues)));
        }

        return $builder->whereIn(new WhereInCondition($this->column, $filteredValues));
    }
}
