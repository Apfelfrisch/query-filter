<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Conditions;

final class OrWhereCondition
{
    public function __construct(
        public readonly string $column,
        public readonly Operator $operator,
        public readonly string|null $value,
    ) {
    }
}
