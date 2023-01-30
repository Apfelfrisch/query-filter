<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Conditions;

use Apfelfrisch\QueryFilter\Exceptions\ConditionException;

final class OrWhereCondition
{
    public function __construct(
        public readonly string $column,
        public readonly Operator $operator,
        public readonly string|null $value,
    ) {
        if ($value === null && ! in_array($this->operator, [Operator::Equal, Operator::NotEqual])) {
            throw ConditionException::invalidOperatorForNullableField($operator);
        }
    }
}
