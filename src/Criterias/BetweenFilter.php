<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Criterias;

use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Exceptions\CriteriaException;
use Apfelfrisch\QueryFilter\QueryBuilder;

final class BetweenFilter implements Filter
{
    private string $column;

    public function __construct(
        private string $name,
        private string|null $beginn = null,
        private string|null $end = null,
    ) {
        $this->column = $this->name;
    }

    public static function new(string $name, string|null $beginn = null, string|null $end = null): self
    {
        return new self($name, $beginn, $end);
    }

    public function forColumn(string $column): self
    {
        $this->column = $column;

        return $this;
    }

    /** @param string|array<int, string> $value */
    public function setValue(string|array $value): self
    {
        if (! is_array($value) || count($value) !== 2) {
            throw new CriteriaException("Value for " . BetweenFilter::class . " has to be an array with two strings.");
        }

        $this->beginn = $value[0];
        $this->end = $value[1];

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function apply(QueryBuilder $builder): QueryBuilder
    {
        if ($this->beginn === null || $this->end === null) {
            return $builder;
        }

        return $builder->where(
            new WhereCondition($this->column, Operator::GreaterThenEqual, $this->beginn),
            new WhereCondition($this->column, Operator::LessThanEqual, $this->end),
        );
    }
}
