<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Conditions;

use Apfelfrisch\QueryFilter\Exceptions\ConditionException;

final class WhereInCondition
{
    /** @param array<int, string> $values */
    public function __construct(
        public readonly string $column,
        public readonly array $values,
    ) {
        if (in_array(null, $values)) {
            throw new ConditionException('Nullable values are not allowd for WhereInConditions.');
        }
    }
}
