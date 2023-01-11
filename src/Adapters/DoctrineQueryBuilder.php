<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Adapters;

use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\OrWhereCondition;
use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereInCondition;
use Apfelfrisch\QueryFilter\QueryBuilder;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Query\QueryBuilder as BaseQueryBuilder;
use Exception;

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

            $this->builder->setParameter(":$where->field", $where->value);
        }

        $this->builder->andWhere($expression);

        return $this;
    }

    public function whereIn(WhereInCondition $where): self
    {
        $this->builder->andWhere(
            $this->builder->expr()->in($where->field, ":$where->field")
        );

        $this->builder->setParameter(":$where->field", $where->values);

        return $this;
    }

    public function sort(string $field, SortDirection $sortDirection): self
    {
        $this->builder->addOrderBy($field, $sortDirection->value);

        return $this;
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
        return match ($where->operator) {
            Operator::Equal => $this->builder->expr()->eq($where->field, ":$where->field"),
            Operator::GreaterThen => $this->builder->expr()->gt($where->field, ":$where->field"),
            Operator::GreaterThenEqual => $this->builder->expr()->gte($where->field, ":$where->field"),
            Operator::LessThan => $this->builder->expr()->lt($where->field, ":$where->field"),
            Operator::LessThanEqual => $this->builder->expr()->lte($where->field, ":$where->field"),
            Operator::Like => $this->builder->expr()->like($where->field, ":$where->field"),
            default => throw new Exception("Unkown Operator [{$where->operator->value}]"),
        };
    }
}
