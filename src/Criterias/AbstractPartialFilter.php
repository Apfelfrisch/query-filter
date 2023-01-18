<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Criterias;

use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\OrWhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\QueryBuilder;

abstract class AbstractPartialFilter implements Filter
{
    private string $field;

    /** @param string|array<int, string>|null $value */
    public function __construct(
        private string $name,
        private string|array|null $value = null
    ) {
        $this->field = $this->name;
    }

    public function forField(string $field): self
    {
        $this->field = $field;

        return $this;
    }

    /** @param string|array<int, string> $value */
    public function setValue(string|array $value): void
    {
        $this->value = $value;
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
                new WhereCondition($this->field, Operator::Like, $this->prepareValue($value))
            );
        }

        if (count(array_filter($value, strlen(...))) === 0) {
            return $builder;
        }

        $conditions = [];

        foreach (array_filter($value, strlen(...)) as $partialValue) {
            $conditions[] = new OrWhereCondition($this->name, Operator::Like, $this->prepareValue($partialValue));
        }

        return $builder->where(...$conditions);
    }

    abstract protected function prepareValue(string $value): string;
}
