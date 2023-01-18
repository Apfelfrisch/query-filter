<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Criterias;

use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\QueryBuilder;
use InvalidArgumentException;

final class BetweenFilter implements Filter
{
    private string $field;

    public function __construct(
        private string $name,
        private string|null $beginn = null,
        private string|null $end = null,
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
        if (is_array($value) && count($value) === 2) {
            $this->beginn = $value[0];
            $this->end = $value[1];
        }

        throw new InvalidArgumentException("Value for " . BetweenFilter::class . " has to be an array with two strings.");
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function apply(QueryBuilder $builder): QueryBuilder
    {
        return $builder->where(
            new WhereCondition($this->field, Operator::GreaterThenEqual, $this->beginn),
            new WhereCondition($this->field, Operator::LessThanEqual, $this->end),
        );
    }
}
