<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Adapters;

use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\OrWhereCondition;
use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereInCondition;
use Apfelfrisch\QueryFilter\Exceptions\ConditionException;
use Apfelfrisch\QueryFilter\QueryBuilder;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder as BaseQueryBuilder;

/**
 * @implements QueryBuilder<BaseQueryBuilder>
 */
final class DoctrineOrmQueryBuilder implements QueryBuilder
{
    /** @var array<array-key, string> */
    private array $selects = [];

    public function __construct(
        private BaseQueryBuilder $builder,
        private string $alias,
    ) {
    }

    public function select(string ...$selects): self
    {
        if ($this->selects === []) {
            $this->builder->select(...$selects);
            $this->selects = $selects;

            return $this;
        }

        $this->builder->addSelect(...$selects);
        $this->selects = array_merge($this->selects, $selects);

        return $this;
    }

    public function where(WhereCondition|OrWhereCondition ...$wheres): self
    {
        $expression = null;

        foreach ($wheres as $where) {
            $expression = $this->buildWhereExpression($where, $expression);

            if ($where->value !== null) {
                $this->builder->setParameter(":$where->column", $where->value);
            }
        }

        $this->builder->andWhere($expression);

        return $this;
    }

    public function whereIn(WhereInCondition $where): self
    {
        $this->builder->andWhere(
            $this->builder->expr()->in($this->column($where), ":$where->column"),
        );

        $this->builder->setParameter(":$where->column", $where->values);

        return $this;
    }

    public function sort(string $column, SortDirection $sortDirection): self
    {
        $this->builder->addOrderBy($this->column($column), $sortDirection->value);

        return $this;
    }

    public function builder(): BaseQueryBuilder
    {
        return $this->builder;
    }

    private function buildWhereExpression(WhereCondition|OrWhereCondition $where, Composite|null $expression = null): Composite
    {
        if ($expression === null) {
            // The first Expression is always and, deptiy it might be a OrWhereCondition
            return $this->builder->expr()->andX($this->buildOperatorExpression($where));
        }

        if ($where instanceof OrWhereCondition) {
            return $this->builder->expr()->orX($this->buildOperatorExpression($where));
        }

        return $this->builder->expr()->andX($this->buildOperatorExpression($where));
    }

    private function buildOperatorExpression(WhereCondition|OrWhereCondition $where): Comparison|string
    {
        if ($where->value === null) {
            return match ($where->operator) {
                Operator::Equal => $this->builder->expr()->isNull($this->column($where)),
                Operator::NotEqual => $this->builder->expr()->isNotNull($this->column($where)),
                default => throw ConditionException::invalidOperatorForNullableField($where->operator),
            };
        }

        return match ($where->operator) {
            Operator::Equal => $this->builder->expr()->eq($this->column($where), ":$where->column"),
            Operator::NotEqual => $this->builder->expr()->neq($this->column($where), ":$where->column"),
            Operator::GreaterThen => $this->builder->expr()->gt($this->column($where), ":$where->column"),
            Operator::GreaterThenEqual => $this->builder->expr()->gte($this->column($where), ":$where->column"),
            Operator::LessThan => $this->builder->expr()->lt($this->column($where), ":$where->column"),
            Operator::LessThanEqual => $this->builder->expr()->lte($this->column($where), ":$where->column"),
            Operator::Like => $this->builder->expr()->like($this->column($where), ":$where->column"),
            Operator::NotLike => $this->builder->expr()->notLike($this->column($where), ":$where->column"),
        };
    }

    private function column(WhereCondition|OrWhereCondition|WhereInCondition|string $condition): string
    {
        if (! is_string($condition)) {
            $condition = $condition->column;
        }

        if (str_contains($condition, '.')) {
            return $condition;
        }

        return $this->alias . "." . $condition;
    }
}
