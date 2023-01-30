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
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Query\QueryBuilder as BaseQueryBuilder;

/**
 * @implements QueryBuilder<BaseQueryBuilder>
 */
final class DoctrineQueryBuilder implements QueryBuilder
{
    public function __construct(
        private BaseQueryBuilder $builder
    ) {
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
            $this->builder->expr()->in($where->column, ":$where->column")
        );

        $this->builder->setParameter(":$where->column", $where->values);

        return $this;
    }

    public function sort(string $column, SortDirection $sortDirection): self
    {
        $this->builder->addOrderBy($column, $sortDirection->value);

        return $this;
    }

    public function builder(): BaseQueryBuilder
    {
        return $this->builder;
    }

    private function buildWhereExpression(WhereCondition|OrWhereCondition $where, CompositeExpression|null $expression = null): CompositeExpression
    {
        if ($expression === null) {
            // The first Expression is always and, deptiy it might be a OrWhereCondition
            return $this->builder->expr()->and($this->buildOperatorExpression($where));
        }

        if ($where instanceof OrWhereCondition) {
            return $this->builder->expr()->or($expression, $this->buildOperatorExpression($where));
        }

        return $this->builder->expr()->and($expression, $this->buildOperatorExpression($where));
    }

    private function buildOperatorExpression(WhereCondition|OrWhereCondition $where): string
    {
        if ($where->value === null) {
            return match ($where->operator) {
                Operator::Equal => $this->builder->expr()->isNull($where->column),
                Operator::NotEqual => $this->builder->expr()->isNotNull($where->column),
                default => throw ConditionException::invalidOperatorForNullableField($where->operator),
            };
        }

        return match ($where->operator) {
            Operator::Equal => $this->builder->expr()->eq($where->column, ":$where->column"),
            Operator::NotEqual => $this->builder->expr()->neq($where->column, ":$where->column"),
            Operator::GreaterThen => $this->builder->expr()->gt($where->column, ":$where->column"),
            Operator::GreaterThenEqual => $this->builder->expr()->gte($where->column, ":$where->column"),
            Operator::LessThan => $this->builder->expr()->lt($where->column, ":$where->column"),
            Operator::LessThanEqual => $this->builder->expr()->lte($where->column, ":$where->column"),
            Operator::Like => $this->builder->expr()->like($where->column, ":$where->column"),
            Operator::NotLike => $this->builder->expr()->notLike($where->column, ":$where->column"),
        };
    }
}
