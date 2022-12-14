<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Conditions;

final class WhereInCondition
{
    /** @param array<int, string> $values */
    public function __construct(
        public readonly string $field,
        public readonly array $values,
    ) {
    }
}
